# Integrasi Laravel dengan skema Supabase IoT Project Hub

## Sumber kebenaran database
Akun berada di `auth.users` (Supabase Auth); profil berada di `public.profiles`.
Laravel tidak membuat/menggunakan tabel `users`. Model `App\\Models\\User` memetakan
profil ke `public.profiles`. Provider autentikasi Laravel memvalidasi password melalui
Supabase Auth REST API dan mengambil profil dengan access token pengguna.

## Konfigurasi `.env`
Salin `.env.example` menjadi `.env`, lalu isi:
- `SUPABASE_URL`: Project URL dari Supabase > Settings > API
- `SUPABASE_ANON_KEY`: publishable/anon key (bukan service_role key)
- `DB_HOST`, `DB_PASSWORD`: kredensial PostgreSQL pooler jika Laravel juga perlu
  koneksi SQL langsung. Autentikasi web pada patch ini memakai Supabase Auth REST API.

Jangan pernah memasukkan `service_role` key ke browser atau repository.

## Autentikasi dan role
- Guest: tidak memiliki sesi login.
- `mahasiswa` dan `umum`: masuk ke area pengguna.
- `admin`: masuk ke area admin.
- Registrasi hanya mengirim `mahasiswa` atau `umum` sebagai metadata; trigger
  `handle_new_user()` pada SQL utama membuat `profiles` otomatis. Admin hanya
  ditetapkan dari Supabase SQL Editor, misalnya:
  `update public.profiles set role = 'admin' where email = 'admin@domain.ac.id';`

Pastikan trigger `on_auth_user_created` dari SQL utama sudah aktif dan email
dikonfirmasi sesuai pengaturan Supabase Auth.

## Foto
Tambahan opsional untuk URL foto ada di `database/supabase/02_add_image_urls.sql`.
Jalankan di SQL Editor jika fitur foto digunakan. File upload perlu dikonfigurasi
ke Supabase Storage atau disk publik Laravel sesuai deployment.

## Catatan cakupan
Struktur SQL Supabase memiliki 17 tabel. Patch ini menyelaraskan lapisan autentikasi
dan profil ke `auth.users` + `public.profiles`, menghapus migrasi Laravel yang
membuat tabel `users`, dan menyediakan SQL tambahan kolom foto.
Controller proyek, inventaris, peminjaman, dan transaksi pada project asal masih
memiliki bagian data demo berbasis session; bagian CRUD tersebut belum seluruhnya
dipindahkan ke tabel Supabase. Jangan menganggap seluruh 17 modul sudah tersambung
hanya dari patch autentikasi ini.
