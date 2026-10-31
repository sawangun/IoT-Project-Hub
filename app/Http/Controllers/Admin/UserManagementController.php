<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    private function users(): array
    {
        return [
            1 => ['id'=>1,'initial'=>'RP','name'=>'Raka Pratama','identity'=>'2205346081','role'=>'Mahasiswa Riset TA','program'=>'S1 Teknik Elektro','semester'=>'Semester 6 • Reguler','rfid'=>'5A:3F:88:C2','locker'=>'Izin: Kios 24/7 (Solenoid)','project'=>'Smart Parking System','project_meta'=>'KIT ESP32 + Ultrasonic HC-SR04','status'=>'Active (Bay A-03)','email'=>'raka.pratama@student.sawangun.ac.id','phone'=>'+62 812-9843-1120','advisor'=>'Dr. Ir. Hendra Gunawan, M.T.'],
            2 => ['id'=>2,'initial'=>'SN','name'=>'Siti Nurhaliza','identity'=>'2105356140','role'=>'Mahasiswa MBKM Riset','program'=>'S1 Teknik Komputer','semester'=>'Semester 8 • Lab Smart Agri','rfid'=>'7B:19:44:E1','locker'=>'Izin: Kios 24/7 (Solenoid)','project'=>'Hydroponic IoT Monitor','project_meta'=>'Sensor pH, EC & Temp Relay','status'=>'Token Terbit (Bay B-01)','email'=>'siti.nurhaliza@student.sawangun.ac.id','phone'=>'+62 812-5551-2210','advisor'=>'Dr. Ir. Hendra Gunawan, M.T.'],
            3 => ['id'=>3,'initial'=>'WH','name'=>'Dr. Ir. Wahyu Hidayat, M.T.','identity'=>'197804152003121002','role'=>'Dosen Pembimbing & Reviewer','program'=>'Teknik Elektro & Robotika','semester'=>'Kepala Laboratorium IoT','rfid'=>'MASTER OVERRIDE','locker'=>'Otorisasi Emergency Unlocking','project'=>'Supervisor MBKM Hub','project_meta'=>'14 Proyek Bimbingan Aktif','status'=>'Verifikator Resmi','email'=>'wahyu.hidayat@sawangun.ac.id','phone'=>'+62 811-3300-1200','advisor'=>'—'],
            4 => ['id'=>4,'initial'=>'BA','name'=>'Bagas Aryasatya','identity'=>'2205336049','role'=>'Tersuspend • Kompen Aktif','program'=>'S1 Informatika','semester'=>'Semester 6 • Lab AI & IoT','rfid'=>'9C:88:12:AA','locker'=>'Izin Akses Locker Diblokir','project'=>'Edge AI Vision Kit','project_meta'=>'Kamera OpenMV H7 Plus','status'=>'Overdue (+3 Hari)','email'=>'bagas.aryasatya@student.sawangun.ac.id','phone'=>'+62 813-9912-1122','advisor'=>'Dr. Ir. Hendra Gunawan, M.T.'],
            5 => ['id'=>5,'initial'=>'MD','name'=>'Maya Dhinata','identity'=>'2205356037','role'=>'Mahasiswa Riset TA','program'=>'S1 Teknik Komputer','semester'=>'Semester 6 • Reguler','rfid'=>'3D:4E:99:B5','locker'=>'Izin: Kios 24/7 (Solenoid)','project'=>'Drone Telemetry Node','project_meta'=>'ESP-NOW + GPS Module','status'=>'Active (Bay B-01)','email'=>'maya.dhinata@student.sawangun.ac.id','phone'=>'+62 812-4455-7788','advisor'=>'Dr. Ir. Hendra Gunawan, M.T.'],
            6 => ['id'=>6,'initial'=>'FF','name'=>'Fadhil Faturrahman','identity'=>'2105346029','role'=>'Mahasiswa Reguler','program'=>'S1 Teknik Elektro','semester'=>'Semester 8 • Lab Transmisi','rfid'=>'1F:22:67:E8','locker'=>'Izin: Jam Lab 08:00 - 18:00','project'=>'LoRaWAN Multi-Hop','project_meta'=>'Proyek Telah Selesai (Diarsip)','status'=>'Bebas Peminjaman (Clean)','email'=>'fadhil.faturrahman@student.sawangun.ac.id','phone'=>'+62 852-2200-7788','advisor'=>'Dr. Ir. Hendra Gunawan, M.T.'],
        ];
    }

    public function index() { return view('admin.users.index', ['users'=>$this->users()]); }
    public function create() { return view('admin.users.create'); }
    public function store(Request $request) { return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil didaftarkan (mode frontend/demo).'); }
    public function show(int $user) { $users=$this->users(); abort_unless(isset($users[$user]),404); return view('admin.users.show',['user'=>$users[$user]]); }
    public function profile() { return view('admin.profile'); }
    public function help() { return view('admin.help'); }
}
