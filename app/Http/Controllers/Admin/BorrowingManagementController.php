<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BorrowingManagementController extends Controller
{
    private function borrowings(): array
    {
        if (! session()->has('admin_borrowings')) {
            $seed = [
                ['id'=>1,'code'=>'BRW-2026-081','name'=>'Ahmad Dahlan','nim'=>'2101010088','program'=>'S1 Teknik Informatika','project'=>'Smart Hydroponic Greenhouse','items'=>'ESP32 DevKit V1, Sensor pH/EC','locker'=>'BAY A-04','duration'=>12,'start'=>'2026-10-28','due'=>'2026-10-30','status'=>'active','notes'=>'Untuk pengujian sensor nutrisi.'],
                ['id'=>2,'code'=>'BRW-2026-082','name'=>'Raka Pratama','nim'=>'2102030142','program'=>'S1 Teknik Elektro','project'=>'LoRaWAN Micro Weather Station','items'=>'LoRa SX1276, BME280','locker'=>'BAY A-03','duration'=>7,'start'=>'2026-10-29','due'=>'2026-11-05','status'=>'waiting','notes'=>'Dipakai untuk praktikum telemetri.'],
                ['id'=>3,'code'=>'BRW-2026-083','name'=>'Siti Nurhaliza','nim'=>'2201010037','program'=>'S1 Teknik Komputer','project'=>'Edge AI Smart Gate','items'=>'Raspberry Pi 4, Camera Module','locker'=>'BAY C-01','duration'=>14,'start'=>'2026-10-30','due'=>'2026-11-13','status'=>'pending','notes'=>'Pengujian model deteksi objek.'],
                ['id'=>4,'code'=>'BRW-2026-084','name'=>'Bagas Aryasatya','nim'=>'2101040061','program'=>'S1 Teknik Informatika','project'=>'Wearable SpO2 Monitor','items'=>'ESP32-C3, MAX30102','locker'=>'BAY B-04','duration'=>3,'start'=>'2026-10-20','due'=>'2026-10-23','status'=>'overdue','notes'=>'Belum ada konfirmasi pengembalian.'],
                ['id'=>5,'code'=>'BRW-2026-085','name'=>'Fadhil Faturrahman','nim'=>'2202030082','program'=>'S1 Teknik Elektro','project'=>'Smart Hydroponic Greenhouse','items'=>'Relay 4-Channel, Sensor pH','locker'=>'BAY B-02','duration'=>7,'start'=>'2026-10-30','due'=>'2026-11-06','status'=>'active','notes'=>''],
                ['id'=>6,'code'=>'BRW-2026-086','name'=>'Maya Dianita','nim'=>'2201010119','program'=>'S1 Teknik Informatika','project'=>'LoRaWAN Micro Weather Station','items'=>'STM32L0, BME280','locker'=>'BAY A-05','duration'=>14,'start'=>'2026-10-30','due'=>'2026-11-13','status'=>'returned','notes'=>'Dikembalikan lengkap.'],
            ];
            session(['admin_borrowings' => $seed]);
        }
        return session('admin_borrowings', []);
    }

    public function index(Request $request): View
    {
        $all = collect($this->borrowings());
        $q = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');
        if ($q !== '') {
            $needle = Str::lower($q);
            $all = $all->filter(fn ($row) => str_contains(Str::lower(implode(' ', [$row['code'],$row['name'],$row['nim'],$row['project'],$row['items'],$row['locker']])), $needle));
        }
        if ($status !== '') $all = $all->where('status', $status);

        $base = collect($this->borrowings());
        return view('admin.borrowings.index', [
            'borrowings'=>$all->values(), 'q'=>$q, 'status'=>$status,
            'total'=>$base->count(), 'active'=>$base->where('status','active')->count(),
            'pending'=>$base->where('status','pending')->count() + $base->where('status','waiting')->count(),
            'overdue'=>$base->where('status','overdue')->count(),
        ]);
    }

    public function create(): View
    {
        return view('admin.borrowings.form', ['borrowing'=>null, 'mode'=>'create']);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $rows = $this->borrowings();
        $id = (count($rows) ? max(array_column($rows, 'id')) : 0) + 1;
        $data['id'] = $id;
        $data['code'] = 'BRW-'.now()->format('Y').'-'.str_pad((string)(80 + $id), 3, '0', STR_PAD_LEFT);
        $data['start'] = $data['start'] ?? now()->format('Y-m-d');
$data['due'] = \Carbon\Carbon::parse($data['start'])->addDays((int)$data['duration'])->format('Y-m-d');
        array_unshift($rows, $data);
        session(['admin_borrowings'=>$rows]);
        return redirect()->route('admin.borrowings.index')->with('success', 'Peminjaman baru berhasil dibuat. Data masih tersimpan sementara di session.');
    }

    public function updateStatus(Request $request, int $borrowing): RedirectResponse
    {
        $data = $request->validate(['status'=>['required','in:pending,waiting,active,rejected,returned,overdue']]);
        $rows = $this->borrowings();
        $found = false;
        foreach ($rows as &$row) {
            if ($row['id'] === $borrowing) {
                $row['status'] = $data['status'];
                $found = true;
                break;
            }
        }
        unset($row);
        abort_if(!$found, 404);
        session(['admin_borrowings'=>$rows]);
        return redirect()->route('admin.borrowings.index')->with('success', 'Status peminjaman berhasil diperbarui.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'=>['required','string','max:100'],
            'nim'=>['required','string','max:30'],
            'program'=>['required','string','max:100'],
            'project'=>['required','string','max:140'],
            'items'=>['required','string','max:240'],
            'locker'=>['required','string','max:40'],
            'duration'=>['required','integer','in:3,7,14,30'],
            'start'=>['required','date'],
            'status'=>['required','in:pending,waiting,active'],
            'notes'=>['nullable','string','max:500'],
        ]);
    }
}
