<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InventoryManagementController extends Controller
{
    private function lockers()
    {
        return DB::table('lockers')->orderBy('code')->get()->map(fn($r)=>[
            'id'=>(int)$r->id,'code'=>$r->code,'location'=>$r->location ?? 'Lab IoT',
            'status'=>$r->status,'item'=>$r->item ?? '',
            'updated_at'=>\Carbon\Carbon::parse($r->updated_at ?? now())->format('Y-m-d H:i'),
        ]);
    }

    private function components()
    {
        return DB::table('components')->orderBy('name')->get()->map(fn($r)=>[
            'id'=>(int)$r->id,'name'=>$r->name,'code'=>$r->code ?? 'CMP-'.$r->id,
            'category'=>$r->category ?? 'Komponen','stock'=>(int)$r->stock,
            'minimum'=>(int)$r->low_stock_limit,'unit'=>$r->unit ?? 'unit',
            'location'=>$r->location ?? 'Gudang Lab',
            'condition'=>$r->stock <= $r->low_stock_limit ? 'low' : 'available',
            'description'=>$r->description ?? $r->spec ?? '',
        ]);
    }

    private function kits()
    {
        return DB::table('kits')->orderBy('name')->get()->map(function($r){
            $items = DB::table('kit_items as ki')->join('components as c','c.id','=','ki.component_id')
                ->where('ki.kit_id',$r->id)->select('c.name','ki.qty')->get()
                ->map(fn($i)=>$i->name.' ('.$i->qty.')')->implode(', ');
            return ['id'=>(int)$r->id,'code'=>$r->code ?? 'KIT-'.$r->id,'name'=>$r->name,
                'category'=>$r->category ?? 'Umum','items'=>$items,'quantity'=>(int)$r->stock,
                'status'=>$r->stock <= 2 ? 'low' : 'available','description'=>$r->description ?? ''];
        });
    }


    public function hardwareUnitsIndex(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');
        $projectId = $request->integer('project_id') ?: null;
        $query = DB::table('hardware_units as hu')->join('projects as p', 'p.id', '=', 'hu.project_id')
            ->select('hu.id', 'hu.project_id', 'hu.serial_code', 'hu.condition', 'hu.status', 'p.title as project_title');
        if ($q !== '') $query->where(function ($w) use ($q) { $w->where('hu.serial_code', 'ilike', "%{$q}%")->orWhere('p.title', 'ilike', "%{$q}%"); });
        if ($status !== '') $query->where('hu.status', $status);
        if ($projectId) $query->where('hu.project_id', $projectId);
        $units = $query->orderBy('p.title')->orderBy('hu.serial_code')->get();
        $projects = DB::table('projects')->orderBy('title')->get(['id', 'title', 'status']);
        return view('admin.inventory.hardware-units', compact('units', 'projects', 'q', 'status', 'projectId'));
    }

    public function storeHardwareUnit(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'serial_code' => ['required', 'string', 'max:120', 'unique:hardware_units,serial_code'],
            'condition' => ['required', 'in:baik,perlu_perbaikan,rusak'],
            'status' => ['required', 'in:available,borrowed,maintenance'],
        ]);
        DB::table('hardware_units')->insert($data);
        return redirect()->route('admin.inventory.hardware-units')->with('success', 'Unit hardware berhasil ditambahkan ke Supabase.');
    }

    public function updateHardwareUnit(Request $request, int $unit): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:available,borrowed,maintenance'], 'condition' => ['required', 'in:baik,perlu_perbaikan,rusak']]);
        $updated = DB::table('hardware_units')->where('id', $unit)->update($data);
        abort_if(!$updated, 404);
        return back()->with('success', 'Status dan kondisi unit berhasil diperbarui.');
    }

    public function lockersIndex(Request $request): View
    {
        $all=$this->lockers(); $q=trim((string)$request->query('q','')); $status=(string)$request->query('status','');
        $rows=$all->filter(fn($r)=>(!$q || str_contains(Str::lower($r['code'].' '.$r['location'].' '.$r['item']),Str::lower($q))) && (!$status || $r['status']===$status))->values();
        return view('admin.inventory.lockers',['lockers'=>$rows,'q'=>$q,'status'=>$status,'all'=>$all]);
    }

    public function lockerStatus(Request $request,int $locker): RedirectResponse
    {
        $data=$request->validate(['status'=>['required','in:available,occupied,maintenance,offline']]);
        $updated=DB::table('lockers')->where('id',$locker)->update([
            'status'=>$data['status'],'updated_at'=>now(),
        ]);
        abort_if(!$updated,404);
        return back()->with('success','Status locker berhasil diperbarui di Supabase.');
    }

    public function kitsIndex(Request $request): View
    {
        $all=$this->kits(); $components=$this->components();
        $q=trim((string)$request->query('q','')); $category=(string)$request->query('category','');
        $rows=$all->filter(fn($r)=>(!$q || str_contains(Str::lower(implode(' ',[$r['name'],$r['code'],$r['items']])),Str::lower($q))) && (!$category || $r['category']===$category))->values();
        return view('admin.inventory.kits',['kits'=>$rows,'all'=>$all,'components'=>$components,'q'=>$q,'category'=>$category]);
    }

    public function createKit(): View { return view('admin.inventory.kit-form',['components'=>$this->components()]); }

    public function storeKit(Request $request): RedirectResponse
    {
        $data=$request->validate(['name'=>['required','string','max:120'],'category'=>['required','string','max:80'],'quantity'=>['required','integer','min:0','max:9999'],'description'=>['nullable','string','max:500'],'components'=>['required','array','min:1'],'components.*'=>['integer','exists:components,id']]);
        $projectId = $request->integer('project_id') ?: DB::table('projects')->value('id');
        abort_unless($projectId, 422, 'Buat minimal satu proyek terlebih dahulu sebelum membuat kit.');
        $kitId=DB::table('kits')->insertGetId([
            'project_id'=>$projectId,
            'name'=>$data['name'],'price'=>0,'stock'=>$data['quantity'],
            'code'=>'KIT-'.str_pad((string)(DB::table('kits')->max('id')+1),3,'0',STR_PAD_LEFT),
            'category'=>$data['category'],'description'=>$data['description'] ?? null,
            'created_at'=>now(),'updated_at'=>now(),
        ]);
        foreach(array_unique($data['components']) as $componentId){
            DB::table('kit_items')->insert(['kit_id'=>$kitId,'component_id'=>$componentId,'qty'=>1]);
        }
        return redirect()->route('admin.inventory.kits')->with('success','Paket kit berhasil disimpan ke Supabase.');
    }

    public function createComponent(): View { return view('admin.inventory.component-form'); }

    public function storeComponent(Request $request): RedirectResponse
    {
        $data=$request->validate(['name'=>['required','string','max:120'],'category'=>['required','string','max:80'],'stock'=>['required','integer','min:0','max:99999'],'minimum'=>['required','integer','min:0','max:99999'],'unit'=>['required','string','max:30'],'location'=>['required','string','max:80'],'description'=>['nullable','string','max:500']]);
        DB::table('components')->insert([
            'name'=>$data['name'],'spec'=>$data['description'] ?? null,'price'=>0,'stock'=>$data['stock'],
            'low_stock_limit'=>$data['minimum'],'code'=>'CMP-'.str_pad((string)(DB::table('components')->max('id')+1),3,'0',STR_PAD_LEFT),
            'category'=>$data['category'],'unit'=>$data['unit'],'location'=>$data['location'],
            'description'=>$data['description'] ?? null,'created_at'=>now(),'updated_at'=>now(),
        ]);
        return redirect()->route('admin.inventory.kits')->with('success','Komponen berhasil disimpan ke Supabase.');
    }
}
