<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BorrowingManagementController extends Controller
{
    private function rows()
    {
        return DB::table('borrowings as b')
            ->join('profiles as u','u.id','=','b.user_id')
            ->join('hardware_units as hu','hu.id','=','b.unit_id')
            ->join('projects as p','p.id','=','hu.project_id')
            ->leftJoin('lockers as l','l.id','=','b.locker_id')
            ->select('b.*','u.name','u.nim','u.email','p.title as project_title','hu.serial_code','l.code as locker_code')
            ->orderByDesc('b.created_at')->get()->map(fn($r)=>[
                'id'=>(int)$r->id,'code'=>'BRW-'.date('Y',strtotime($r->created_at)).'-'.str_pad((string)$r->id,4,'0',STR_PAD_LEFT),
                'name'=>$r->name,'nim'=>$r->nim ?? '—','program'=>'—','project'=>$r->project_title,
                'items'=>$r->serial_code,'locker'=>$r->locker_code ?? 'Belum dialokasikan',
                'duration'=>\Carbon\Carbon::parse($r->start_date)->diffInDays(\Carbon\Carbon::parse($r->due_date)),
                'start'=>$r->start_date,'due'=>$r->due_date,'status'=>$r->status,'notes'=>$r->notes ?? '',
                'return_photo_path'=>$r->return_photo_path ?? null,'return_photo_taken_at'=>$r->return_photo_taken_at ?? null,
                'return_condition'=>$r->return_condition ?? null,'return_notes'=>$r->return_notes ?? null,
            ]);
    }

    public function index(Request $request): View
    {
        $base = $this->rows();
        $q = trim((string)$request->query('q',''));
        $status = (string)$request->query('status','');
        $rows = $base;
        if ($q !== '') $rows = $rows->filter(fn($r)=>str_contains(Str::lower(implode(' ',[$r['code'],$r['name'],$r['nim'],$r['project'],$r['items'],$r['locker']])),Str::lower($q)));
        if ($status !== '') $rows = $rows->where('status',$status);
        return view('admin.borrowings.index',[
            'borrowings'=>$rows->values(),'q'=>$q,'status'=>$status,'total'=>$base->count(),
            'active'=>$base->whereIn('status',['active','overdue'])->count(),
            'pending'=>$base->where('status','pending')->count(),
            'overdue'=>$base->where('status','overdue')->count(),
        ]);
    }

    public function create(): View
    {
        return view('admin.borrowings.form',[
            'borrowing'=>null,'mode'=>'create',
            'users'=>DB::table('profiles')->select('id','name','nim','email')->orderBy('name')->get(),
            'units'=>DB::table('hardware_units as hu')->join('projects as p','p.id','=','hu.project_id')
                ->where('hu.status','available')->select('hu.id','hu.serial_code','p.title')->orderBy('p.title')->get(),
            'lockers'=>DB::table('lockers')->where('status','available')->orderBy('code')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id'=>['required','uuid','exists:profiles,id'],
            'unit_id'=>['required','integer','exists:hardware_units,id'],
            'locker_id'=>['nullable','integer','exists:lockers,id'],
            'start'=>['required','date'],
            'duration'=>['required','integer','in:3,7,14,30'],
            'notes'=>['nullable','string','max:500'],
        ]);
        $unit = DB::table('hardware_units')->where('id',$data['unit_id'])->first();
        abort_unless($unit && $unit->status === 'available',422,'Unit tidak tersedia.');
        $start = \Carbon\Carbon::parse($data['start']);
        DB::table('borrowings')->insert([
            'user_id'=>$data['user_id'],'unit_id'=>$data['unit_id'],'locker_id'=>$data['locker_id'] ?: null,
            'status'=>'pending','start_date'=>$start->toDateString(),
            'due_date'=>$start->copy()->addDays((int)$data['duration'])->toDateString(),
            'notes'=>$data['notes'] ?? null,'created_at'=>now(),'updated_at'=>now(),
        ]);
        return redirect()->route('admin.borrowings.index')->with('success','Pengajuan peminjaman berhasil dibuat di Supabase dengan status menunggu persetujuan.');
    }

    public function updateStatus(Request $request, int $borrowing): RedirectResponse
    {
        $data = $request->validate(['status'=>['required','in:approved,active,overdue,returned,rejected,cancelled']]);
        $loan = DB::table('borrowings')->where('id',$borrowing)->first();
        abort_if(!$loan,404);
        $update = ['status'=>$data['status'],'updated_at'=>now()];
        if ($data['status']==='approved') $update['approved_by']=auth()->id();
        if ($data['status']==='active' && !$loan->picked_up_at) $update['picked_up_at']=now();
        if ($data['status']==='returned') $update['returned_at']=now();
        DB::table('borrowings')->where('id',$borrowing)->update($update);
        return redirect()->route('admin.borrowings.index')->with('success','Status peminjaman berhasil diperbarui di Supabase.');
    }
}
