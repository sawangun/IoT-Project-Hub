<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * Sumber data statis. Ganti dengan Eloquent model jika sudah memakai database.
 */
class HubData
{
    public static function stats(): array
    {
        return [
            ['value' => '12+', 'label' => 'Lab Fakultas Teknik'],
            ['value' => '48 Komp.', 'label' => 'Loker Pintar Aktif'],
            ['value' => '1.450+', 'label' => 'Mahasiswa Periset'],
            ['value' => 'SSO & Dikti', 'label' => 'Sinkron Otomatis'],
        ];
    }

    public static function lockers(): array
    {
        return [
            ['name' => 'Cabinet A • Terpadu Lt. 2', 'ready' => 18, 'total' => 24, 'online' => true],
            ['name' => 'Cabinet B • Riset Energi', 'ready' => 9, 'total' => 16, 'online' => true],
            ['name' => 'Cabinet C • Robotika', 'ready' => 12, 'total' => 20, 'online' => true],
        ];
    }

    public static function features(): array
    {
        return [
            ['icon' => 'lock', 'title' => 'Peminjaman Mandiri 24/7', 'desc' => 'Ambil dan kembalikan modul proyek kapan saja melalui Smart Locker otonom dengan otentikasi QR token & PIN dinamis.'],
            ['icon' => 'check', 'title' => 'Kit Terkalibrasi & Teruji', 'desc' => 'Modul ESP32, STM32, LoRaWAN, hingga Edge AI Jetson telah melalui burn-in testing teknisi lab dan pengecekan osiloskop berkala.'],
            ['icon' => 'code', 'title' => 'Repositori Open-Hardware', 'desc' => 'Akses gratis skematik KiCad, layout PCB Gerber, source code firmware PlatformIO, dan wiring diagram lengkap tervalidasi.'],
            ['icon' => 'scale', 'title' => 'Sensor Beban Loadcell', 'desc' => 'Sensor timbangan digital terintegrasi memastikan integritas kelengkapan komponen saat serah-terima pada inspeksi manual asisten.'],
        ];
    }

    public static function projects(): Collection
    {
        return collect([
            ['slug' => 'smart-hydroponic-greenhouse', 'title' => 'Smart Hydroponic Greenhouse Auto Dosing', 'lab' => 'Lab Agro-Teknologi', 'locker' => 'Loker B-02', 'rating' => 4.9, 'reviews' => 38, 'desc' => 'Pengontrolan pH, nutrisi PPM EC otomatis, dan telemetri Blynk/MQTT real-time.', 'tags' => ['ESP32', 'Sensor pH/EC', 'Relay 4-Ch'], 'image' => 'https://images.unsplash.com/photo-1530836369250-ef72a3f5cda8?w=600'],
            ['slug' => 'lora-micro-weather-station', 'title' => 'LoRaWAN Micro Weather Station Ultra-Low', 'lab' => 'Lab Telekomunikasi', 'locker' => 'Loker A-05', 'rating' => 5.0, 'reviews' => 45, 'desc' => 'Stasiun cuaca jarak jauh bertenaga surya 10km LoRaWAN The Things Network.', 'tags' => ['STM32L0', 'LoRa SX1276', 'BME280'], 'image' => 'https://images.unsplash.com/photo-1561484930-998b6a7b22e8?w=600'],
            ['slug' => 'edge-ai-anpr-gate', 'title' => 'Edge AI Smart Gate & ANPR Reader', 'lab' => 'Lab Artificial Intelligence', 'locker' => 'Loker C-01', 'rating' => 4.8, 'reviews' => 27, 'desc' => 'Deteksi plat kendaraan realtime memanfaatkan model YOLOv8 on-device.', 'tags' => ['Raspberry Pi 4', 'OV5647 Cam', 'Servo MG996R'], 'image' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=600'],
            ['slug' => 'wearable-spo2-heart-rate', 'title' => 'Wearable SpO2 & Heart-Rate Monitor', 'lab' => 'Lab Biomedika', 'locker' => 'Loker B-04', 'rating' => 4.9, 'reviews' => 19, 'desc' => 'Perangkat telemetri pasien real-time berbasis BLE dengan algoritma filter.', 'tags' => ['ESP32-C3', 'MAX30102 PPG', 'OLED 0.96"'], 'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600'],
        ]);
    }

    public static function steps(): array
    {
        return [
            ['no' => '01', 'title' => 'Pilih Proyek / Kit', 'desc' => 'Temukan modul sensor atau board mikrokontroler di katalog terpadu berdasarkan topik riset tugas akhir.', 'note' => 'Realtime Stock Check'],
            ['no' => '02', 'title' => 'QR Pass Dinamis', 'desc' => 'Otorisasi kilat via SSO akun kampus mahasiswa. Sistem menerbitkan token QR berlaku 30 menit ke smartphone.', 'note' => 'Enkripsi AES-256'],
            ['no' => '03', 'title' => 'Scan di Smart Locker', 'desc' => 'Datangi kabinet loker terdekat di fakultas, arahkan layar ponsel ke scanner kamera berpresisi tinggi.', 'note' => 'Respons Scanner < 0.8s'],
            ['no' => '04', 'title' => 'Pintu Terbuka & Riset', 'desc' => 'Solenoid lock membuka loker otomatis, sensor loadcell mengonfirmasi penarikan wadah, dan mulailah eksperimenmu!', 'note' => 'Auto Weighing Verification'],
        ];
    }

    public static function testimonials(): array
    {
        return [
            ['name' => 'Rian Pratama', 'role' => 'Teknik Elektro • Juara 1 Pimnas 2024', 'quote' => 'Smart locker benar-benar penyelamat pas ngejar tugas akhir sistem monitoring irigasi. Butuh ganti board ESP32 malam jam 11 karena short circuit, tinggal scan QR di lab tanpa nunggu kantor buka besoknya.'],
            ['name' => 'Adinda Nurul', 'role' => 'Teknik Komputer • Periset Edge AI', 'quote' => 'Repositori skematik KiCad terverifikasi sangat mempercepat riset kami. Layout PCB dan pinoutnya presisi, jadi tim kami bisa fokus ke optimasi model machine learning TinyML di mikrokontroler.'],
            ['name' => 'Farhan Maulidin', 'role' => 'Teknik Mekatronika • Skripsi 2024', 'quote' => 'Fitur loadcell di lokernya keren banget. Waktu balikin sensor gas & modul LoRa, sistem langsung mendeteksi beratnya pas, checklist pengembalian otomatis beres tanpa drama tuduh komponen hilang.'],
        ];
    }

    public static function footerLinks(): array
    {
        return [
            'Navigasi Cepat' => ['Beranda Utama', 'Repositori Project', 'Katalog Sensor & Kit', 'Layanan Smart Locker', 'Fasilitas & Jadwal Lab'],
            'Kategori IoT' => ['Smart Agriculture', 'LoRa & Telemetri Jauh', 'Industrial Automation', 'Edge AI & Computer Vision', 'Smart City Infrastructure'],
            'Dukungan & Regulasi' => ['Pedoman Keselamatan Lab', 'SOP Peminjaman Alat', 'FAQ Mahasiswa', 'Pengajuan Hibah Riset', 'Kontak Helpdesk Kampus'],
        ];
    }
}
