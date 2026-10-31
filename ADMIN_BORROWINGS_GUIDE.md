# Admin Borrowings

Halaman tambahan untuk manajemen peminjaman di Sawangun IoT Hub.

## Routes
- `/admin/borrowings` — daftar dan manajemen peminjaman
- `/admin/borrowings/create` — formulir peminjaman baru

## Fitur demo
- Data contoh dimuat ke session Laravel saat halaman pertama kali dibuka.
- Pencarian peminjam, NIM, kode, proyek, komponen, dan loker.
- Filter status.
- Membuat peminjaman baru dengan hitung jatuh tempo otomatis.
- Mengubah status: menunggu persetujuan → disetujui/menunggu pengambilan → sedang dipinjam → dikembalikan, atau ditolak.
- Tidak membutuhkan tabel database khusus.

Data session dapat hilang ketika session berakhir. Ini hanya untuk demo, bukan penyimpanan permanen.
