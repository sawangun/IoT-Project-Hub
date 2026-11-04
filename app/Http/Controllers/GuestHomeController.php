<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class GuestHomeController extends Controller
{
    public function index(): View
    {
        $projects = [
            [
                'name' => 'Smart Hydroponic Greenhouse',
                'category' => 'Agro-Teknologi',
                'description' => 'Pemantauan nutrisi dan kondisi tanaman dengan sensor terintegrasi.',
                'image' => 'https://images.unsplash.com/photo-1530836369250-ef72a3f5cda8?auto=format&fit=crop&w=900&q=80',
                'tags' => ['ESP32', 'Sensor pH', 'Relay'],
            ],
            [
                'name' => 'LoRaWAN Weather Station',
                'category' => 'Telemetri',
                'description' => 'Stasiun cuaca hemat energi dengan pengiriman data jarak jauh.',
                'image' => 'https://images.unsplash.com/photo-1504608524841-42fe6f032b4b?auto=format&fit=crop&w=900&q=80',
                'tags' => ['LoRa', 'BME280', 'Solar'],
            ],
            [
                'name' => 'Smart Gate & Vehicle Detection',
                'category' => 'Computer Vision',
                'description' => 'Prototipe gerbang otomatis untuk riset deteksi kendaraan.',
                'image' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=900&q=80',
                'tags' => ['Raspberry Pi', 'OpenCV', 'Servo'],
            ],
            [
                'name' => 'Wearable Health Monitor',
                'category' => 'Biomedis',
                'description' => 'Pemantauan denyut jantung dan saturasi oksigen secara real-time.',
                'image' => 'https://images.unsplash.com/photo-1575311373937-040b8e1fd5b6?auto=format&fit=crop&w=900&q=80',
                'tags' => ['ESP32-C3', 'MAX30102', 'OLED'],
            ],
        ];

        $lockerStats = [
            ['name' => 'Cabinet A · Terpadu', 'available' => 18, 'total' => 24],
            ['name' => 'Cabinet B · IoT', 'available' => 9, 'total' => 16],
            ['name' => 'Cabinet C · Robotika', 'available' => 12, 'total' => 20],
        ];

        return view('guest.home', compact('projects', 'lockerStats'));
    }
}
