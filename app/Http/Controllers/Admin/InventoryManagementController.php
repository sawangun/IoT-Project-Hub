<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InventoryManagementController extends Controller
{
    private function lockers(): array
    {
        if (!session()->has('admin_lockers')) {
            session(['admin_lockers' => collect(range(1, 12))->map(fn ($n) => [
                'id' => $n, 'code' => 'SL-' . str_pad((string)$n, 2, '0', STR_PAD_LEFT),
                'location' => $n <= 6 ? 'Lab IoT A' : 'Lab IoT B',
                'status' => in_array($n, [3, 8]) ? 'occupied' : (in_array($n, [5, 11]) ? 'maintenance' : (in_array($n, [2, 9]) ? 'reserved' : 'available')),
                'item' => in_array($n, [3, 8]) ? ($n === 3 ? 'Sensor Kit A' : 'Arduino Kit') : '',
                'updated_at' => now()->subMinutes($n * 7)->format('Y-m-d H:i'),
            ])->all()]);
        }
        return session('admin_lockers', []);
    }

    private function components(): array
    {
        if (!session()->has('admin_components')) {
            session(['admin_components' => [
                ['id'=>1,'name'=>'ESP32 DevKit V1','code'=>'CMP-001','category'=>'Microcontroller','stock'=>18,'minimum'=>5,'unit'=>'unit','location'=>'Rak A-01','condition'=>'available','description'=>'Board mikrokontroler Wi-Fi dan Bluetooth.'],
                ['id'=>2,'name'=>'DHT22','code'=>'SNS-002','category'=>'Sensor','stock'=>12,'minimum'=>4,'unit'=>'unit','location'=>'Rak A-02','condition'=>'available','description'=>'Sensor suhu dan kelembapan.'],
                ['id'=>3,'name'=>'HC-SR04','code'=>'SNS-003','category'=>'Sensor','stock'=>3,'minimum'=>5,'unit'=>'unit','location'=>'Rak A-03','condition'=>'low','description'=>'Sensor jarak ultrasonik.'],
                ['id'=>4,'name'=>'Breadboard 830 titik','code'=>'CMP-004','category'=>'Prototyping','stock'=>10,'minimum'=>4,'unit'=>'unit','location'=>'Rak B-01','condition'=>'available','description'=>'Papan perakitan rangkaian tanpa solder.'],
                ['id'=>5,'name'=>'Kabel Jumper M-M','code'=>'CMP-005','category'=>'Kabel & konektor','stock'=>2,'minimum'=>5,'unit'=>'set','location'=>'Rak B-02','condition'=>'low','description'=>'Kabel jumper male-to-male.'],
            ]]);
        }
        return session('admin_components', []);
    }

    private function kits(): array
    {
        if (!session()->has('admin_kits')) {
            session(['admin_kits' => [
                ['id'=>1,'code'=>'KIT-001','name'=>'IoT Starter Kit','category'=>'Pemula','items'=>'ESP32, Breadboard, Kabel Jumper','quantity'=>8,'status'=>'available','description'=>'Paket awal untuk eksperimen IoT.'],
                ['id'=>2,'code'=>'KIT-002','name'=>'Environmental Sensor Kit','category'=>'Sensor & lingkungan','items'=>'ESP32, DHT22, Kabel Jumper','quantity'=>5,'status'=>'available','description'=>'Paket pemantauan suhu dan kelembapan.'],
                ['id'=>3,'code'=>'KIT-003','name'=>'Distance Detection Kit','category'=>'Sensor & lingkungan','items'=>'ESP32, HC-SR04, Breadboard','quantity'=>2,'status'=>'low','description'=>'Paket eksperimen sensor jarak.'],
            ]]);
        }
        return session('admin_kits', []);
    }

    public function lockersIndex(Request $request): View
    {
        $all = collect($this->lockers());
        $q = trim((string)$request->query('q', '')); $status = (string)$request->query('status', '');
        $rows = $all->filter(fn($r) => (!$q || str_contains(Str::lower($r['code'].' '.$r['location'].' '.$r['item']), Str::lower($q))) && (!$status || $r['status'] === $status))->values();
        return view('admin.inventory.lockers', ['lockers'=>$rows,'q'=>$q,'status'=>$status,'all'=>$all]);
    }

    public function lockerStatus(Request $request, int $locker): RedirectResponse
    {
        $data = $request->validate(['status'=>['required','in:available,occupied,reserved,maintenance']]);
        $rows = $this->lockers();
        foreach ($rows as &$row) if ($row['id'] === $locker) { $row['status']=$data['status']; if ($data['status'] !== 'occupied') $row['item']=''; $row['updated_at']=now()->format('Y-m-d H:i'); }
        unset($row); session(['admin_lockers'=>$rows]);
        return back()->with('success','Status locker berhasil diperbarui.');
    }

    public function kitsIndex(Request $request): View
    {
        $all = collect($this->kits()); $components = collect($this->components());
        $q=trim((string)$request->query('q','')); $category=(string)$request->query('category','');
        $rows=$all->filter(fn($r)=>(!$q || str_contains(Str::lower(implode(' ', [$r['name'], $r['code'], $r['items']])),Str::lower($q))) && (!$category || $r['category']===$category))->values();
        return view('admin.inventory.kits', ['kits'=>$rows,'all'=>$all,'components'=>$components,'q'=>$q,'category'=>$category]);
    }

    public function createKit(): View { return view('admin.inventory.kit-form', ['components'=>$this->components()]); }

    public function storeKit(Request $request): RedirectResponse
    {
        $data=$request->validate(['name'=>['required','string','max:120'],'category'=>['required','string','max:80'],'quantity'=>['required','integer','min:0','max:9999'],'description'=>['nullable','string','max:500'],'components'=>['required','array','min:1'],'components.*'=>['string','max:120']]);
        $rows=$this->kits(); $id=(count($rows)?max(array_column($rows,'id')):0)+1;
        $rows[]=['id'=>$id,'code'=>'KIT-'.str_pad((string)$id,3,'0',STR_PAD_LEFT),'name'=>$data['name'],'category'=>$data['category'],'items'=>implode(', ',$data['components']),'quantity'=>$data['quantity'],'status'=>$data['quantity']>2?'available':'low','description'=>$data['description']??''];
        session(['admin_kits'=>$rows]); return redirect()->route('admin.inventory.kits')->with('success','Paket kit berhasil ditambahkan ke data session.');
    }

    public function createComponent(): View { return view('admin.inventory.component-form'); }

    public function storeComponent(Request $request): RedirectResponse
    {
        $data=$request->validate(['name'=>['required','string','max:120'],'category'=>['required','string','max:80'],'stock'=>['required','integer','min:0','max:99999'],'minimum'=>['required','integer','min:0','max:99999'],'unit'=>['required','string','max:30'],'location'=>['required','string','max:80'],'description'=>['nullable','string','max:500']]);
        $rows=$this->components(); $id=(count($rows)?max(array_column($rows,'id')):0)+1;
        $rows[]=['id'=>$id,'name'=>$data['name'],'code'=>($data['category']==='Sensor'?'SNS-':'CMP-').str_pad((string)$id,3,'0',STR_PAD_LEFT),'category'=>$data['category'],'stock'=>$data['stock'],'minimum'=>$data['minimum'],'unit'=>$data['unit'],'location'=>$data['location'],'condition'=>$data['stock']<=$data['minimum']?'low':'available','description'=>$data['description']??''];
        session(['admin_components'=>$rows]); return redirect()->route('admin.inventory.kits')->with('success','Komponen/sensor berhasil ditambahkan.');
    }
}
