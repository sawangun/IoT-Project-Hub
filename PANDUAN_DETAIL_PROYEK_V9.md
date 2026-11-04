# Panduan fitur detail proyek v9

Pembaruan ini menambahkan dokumentasi kode, panduan wiring, jumlah suka, dan tautan profil pembuat.

## Fitur
- `repo_url`: tautan repository kode, misalnya GitHub.
- `schematic_url`: tautan diagram/skema rangkaian.
- `wiring_guide`: daftar koneksi kabel, satu koneksi per baris, contoh `DHT22 VCC -> ESP32 3V3`.
- `maker_name`, `maker_instagram_url`, `maker_github_url`, `maker_linkedin_url`: profil pembuat yang tampil pada detail proyek.
- Jumlah suka dihitung dari data yang sudah ada di tabel `saved_projects`; tidak membuat tabel like baru.

## Instalasi
1. Backup folder project dan file `.env`.
2. Ekstrak ZIP dan salin perubahan ke project.
3. Jalankan `composer install` jika folder `vendor` belum tersedia.
4. Jalankan `php artisan migrate` untuk menambahkan kolom opsional baru pada tabel `projects`.
5. Jalankan `php artisan optimize:clear` lalu refresh browser dengan `Ctrl + F5`.

Migration hanya menambahkan kolom nullable baru dan tidak menghapus kolom/data yang sudah ada. Pastikan `.env` mengarah ke database Supabase yang benar sebelum menjalankan migrasi. Jika tidak ingin menjalankan migration, jangan gunakan kolom baru sebelum menambahkannya secara manual.
