# Penyesuaian V9 — Supabase schema sebagai sumber utama

## Yang sudah diselaraskan
- Model autentikasi `User` sekarang memetakan `public.profiles`, bukan tabel `users`.
- Login/logout memakai Supabase Auth REST API; profil dibaca dari `public.profiles`.
- Registrasi mengirim metadata `name`, `role` (`mahasiswa`/`umum`), `nim`, dan `phone` agar trigger `handle_new_user()` pada SQL utama mengisi `profiles`.
- Role admin tidak bisa dipilih saat registrasi. Set admin dari Supabase SQL Editor.
- Middleware `role:pengguna` mengizinkan `mahasiswa` dan `umum`; `role:admin` khusus admin.
- Migrasi Laravel yang membuat tabel `users`, `borrowings`, dan `purchase_orders` versi lokal dihapus agar tidak bentrok dengan skema Supabase.
- SQL skema asli disertakan di `database/supabase/01_schema.sql`.
- Model Eloquent untuk tabel domain ditambahkan.
- SQL tambahan kolom foto ada di `database/supabase/02_add_image_urls.sql`.

## Setup
1. Jalankan `database/supabase/01_schema.sql` satu kali di Supabase SQL Editor hanya bila project Supabase masih kosong, sesuai komentar pada file.
2. Bila tabel sudah ada, jangan jalankan ulang seluruh schema. Jalankan hanya SQL tambahan yang memang belum diterapkan.
3. Jalankan `database/supabase/02_add_image_urls.sql` bila fitur foto diperlukan.
4. Isi `.env` dari `.env.example`: `SUPABASE_URL`, `SUPABASE_ANON_KEY`, dan kredensial PostgreSQL jika modul memakai koneksi SQL langsung.
5. `php artisan config:clear`.

## Membuat admin pertama
Daftarkan akun biasa dahulu dan verifikasi email, lalu jalankan (ganti alamat email):
```sql
update public.profiles set role = 'admin' where email = 'admin@domain.ac.id';
```

## Batasan yang masih perlu dikerjakan
Sebagian controller halaman proyek, inventaris, peminjaman, dan transaksi dari project sebelumnya masih menggunakan data demo/session. Model tabel sudah tersedia, tetapi CRUD 17 tabel belum seluruhnya dikonversi menjadi query Supabase/PostgreSQL. Halaman dan modul tersebut perlu migrasi controller bertahap agar tidak ada klaim palsu bahwa semua fungsi sudah live.
