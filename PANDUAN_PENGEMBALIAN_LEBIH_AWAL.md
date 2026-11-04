# Pengembalian Perangkat Lebih Awal

Pengguna kini dapat mengembalikan perangkat dari halaman **Peminjaman Saya** tanpa menunggu tanggal jatuh tempo.

## Alur
1. Admin menyetujui pengajuan dan menandai unit sebagai sedang dipinjam (status `active`).
2. Pengguna membuka menu Peminjaman Saya.
3. Pada peminjaman berstatus `active` atau `overdue`, pengguna menekan **Kembalikan sekarang** dan mengonfirmasi tindakan.
4. Aplikasi mengubah status peminjaman menjadi `returned` serta mengisi `returned_at`.
5. Trigger Supabase `on_borrowing_status_change()` yang sudah ada akan mengembalikan status unit ke `available`, mengosongkan locker terkait, mencabut QR aktif, dan mencatat notifikasi/log sesuai konfigurasi schema.

## Keamanan
Endpoint hanya menerima peminjaman milik profil pengguna yang sedang login dan hanya mengizinkan status `active` atau `overdue`. Status `pending` dan `approved` tidak dapat dikembalikan karena perangkat belum tercatat diterima/dipinjam oleh pengguna.

## Database
Tidak memerlukan tabel atau migrasi baru. Trigger `public.on_borrowing_status_change()` dari schema Supabase yang sudah dipasang menangani sinkronisasi unit dan locker ketika status menjadi `returned`.
