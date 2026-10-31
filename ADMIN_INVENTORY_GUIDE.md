# Admin Inventory Pages — Sawangun IoT Hub

Tambahan empat halaman admin:

- `/admin/lockers` — Smart Locker Management; filter dan ubah status locker.
- `/admin/kits-components` — daftar paket kit dan komponen/sensor, ringkasan stok, filter.
- `/admin/kits-components/kits/create` — buat paket kit dengan memilih komponen yang tersedia.
- `/admin/kits-components/components/create` — tambah komponen/sensor, kategori, stok, batas minimum, satuan, dan lokasi penyimpanan.

Data demo disimpan menggunakan session, bukan database. Data locker, kit, dan komponen tidak tersinkron dengan perangkat fisik dan tidak mengurangi stok komponen otomatis ketika sebuah kit dibuat.

Jika aplikasi menampilkan error SQLite karena konfigurasi session, pastikan `.env` menggunakan `SESSION_DRIVER=file`, lalu jalankan `php artisan config:clear` dan `php artisan serve`.
