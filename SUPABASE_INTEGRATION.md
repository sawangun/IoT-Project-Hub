# Integrasi Laravel dengan Supabase IoT Project Hub

## Sumber data
Akun berada di `auth.users`, profil berada di `public.profiles`, dan data aplikasi disimpan di tabel Supabase seperti `projects`, `borrowings`, `lockers`, `components`, `kits`, `orders`, dan tabel relasinya.

Laravel menggunakan koneksi PostgreSQL (`DB_CONNECTION=pgsql`) untuk operasi data server-side. Supabase Auth REST API digunakan untuk validasi email/kata sandi. Laravel Session tetap dipakai oleh guard web sebagai cookie/status login dan penyimpanan token akses; session bukan tempat penyimpanan data CRUD.

## Konfigurasi
Isi `.env` dengan:
- `SUPABASE_URL`
- `SUPABASE_ANON_KEY`
- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` dari koneksi PostgreSQL Supabase.

Jangan pernah memasukkan `service_role` key ke browser atau repository. Service role hanya diperlukan untuk operasi administrasi Auth tertentu yang memang dijalankan server-side.

## Role
- `mahasiswa` dan `umum`: area pengguna.
- `admin`: area admin.
- Role admin ditetapkan melalui Supabase SQL Editor, bukan dari form registrasi publik.

## Kolom tambahan
Jalankan `php artisan migrate --force` setelah mengisi konfigurasi PostgreSQL. Migration `2026_11_01_000001_add_admin_metadata_to_supabase_tables.php` menambahkan kolom metadata admin yang tidak ada pada skema inti.

## Catatan
Status draft pada antarmuka proyek dipetakan ke `pending` di enum Supabase. Checkout memakai `orders` dan `order_items` dari skema inti. Simulasi pembayaran bukan transaksi uang sungguhan.
