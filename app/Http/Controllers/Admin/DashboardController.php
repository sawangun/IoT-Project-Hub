<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        // Semua data di bawah ini hanya data contoh (dummy), bukan dari database.
        $adminName = 'Admin';

        $totalProjects = 8;
        $totalBorrowings = 12;
        $activeBorrowings = 5;
        $pendingBorrowings = 3;

        $lockerStats = [
            'total' => 8,
            'available' => 4,
            'occupied' => 2,
            'reserved' => 1,
            'maintenance' => 1,
        ];

        $lockers = [
            ['name' => 'L-01', 'status' => 'available'],
            ['name' => 'L-02', 'status' => 'occupied'],
            ['name' => 'L-03', 'status' => 'available'],
            ['name' => 'L-04', 'status' => 'reserved'],
            ['name' => 'L-05', 'status' => 'occupied'],
            ['name' => 'L-06', 'status' => 'available'],
            ['name' => 'L-07', 'status' => 'maintenance'],
            ['name' => 'L-08', 'status' => 'available'],
        ];

        $weeklyChart = [
            ['label' => 'Sen', 'total' => 2],
            ['label' => 'Sel', 'total' => 4],
            ['label' => 'Rab', 'total' => 3],
            ['label' => 'Kam', 'total' => 6],
            ['label' => 'Jum', 'total' => 5],
            ['label' => 'Sab', 'total' => 2],
            ['label' => 'Min', 'total' => 4],
        ];

        $recentBorrowings = [
            [
                'id' => 1,
                'user_name' => 'Andi Saputra',
                'project_title' => 'Smart Garden',
                'request_date' => '2026-10-30',
                'expires_date' => '2026-11-02',
                'status' => 'pending',
            ],
            [
                'id' => 2,
                'user_name' => 'Siti Rahma',
                'project_title' => 'Smart Door Lock',
                'request_date' => '2026-10-29',
                'expires_date' => '2026-11-03',
                'status' => 'active',
            ],
            [
                'id' => 3,
                'user_name' => 'Budi Firmansyah',
                'project_title' => 'Monitoring Suhu',
                'request_date' => '2026-10-28',
                'expires_date' => null,
                'status' => 'returned',
            ],
        ];

        $kits = [
            [
                'name' => 'Smart Garden Kit',
                'detail' => 'ESP32, soil moisture, relay, kabel',
                'price' => 'Rp250.000',
                'stock' => 8,
                'status' => 'Tersedia',
            ],
            [
                'name' => 'Smart Door Lock Kit',
                'detail' => 'ESP32, solenoid door lock, RFID',
                'price' => 'Rp325.000',
                'stock' => 3,
                'status' => 'Stok menipis',
            ],
            [
                'name' => 'Temperature Sensor Kit',
                'detail' => 'ESP32, sensor DHT11, kabel jumper',
                'price' => 'Rp150.000',
                'stock' => 6,
                'status' => 'Tersedia',
            ],
        ];

        return view('admin.dashboard', compact(
            'adminName',
            'totalProjects',
            'totalBorrowings',
            'activeBorrowings',
            'pendingBorrowings',
            'lockerStats',
            'lockers',
            'weeklyChart',
            'recentBorrowings',
            'kits'
        ));
    }
}
