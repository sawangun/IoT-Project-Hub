# Setup Supabase untuk versi admin yang sudah dipindahkan

1. Pastikan skema utama `database/supabase/01_schema.sql` sudah dijalankan pada project Supabase dan tabel inti tersedia.
2. Salin `.env.example` menjadi `.env`, lalu isi `SUPABASE_URL`, `SUPABASE_ANON_KEY`, serta koneksi PostgreSQL Supabase (`DB_CONNECTION=pgsql`, host, port, database, username, password).
3. Jalankan `composer install`.
4. Jika `APP_KEY` masih kosong, jalankan `php artisan key:generate`.
5. Jalankan `php artisan migrate --force` satu kali. Migration tambahan menambahkan metadata yang dibutuhkan halaman admin tanpa menghapus data yang sudah ada.
6. Jalankan `php artisan optimize:clear`, lalu `php artisan serve`.

## Pembuatan akun dari halaman admin

Fitur tambah pengguna menggunakan Supabase Auth Admin API. Isi `SUPABASE_SERVICE_ROLE_KEY` pada `.env` server saja jika fitur ini akan dipakai. Jangan pernah menaruh key tersebut pada file JavaScript/Vite atau membagikannya ke browser.

## Catatan

- Laravel Session tetap digunakan oleh guard web untuk status login dan token Supabase. Data CRUD tidak disimpan di session.
- Jangan menjalankan ulang `01_schema.sql` pada database yang sudah berisi tabel karena file itu ditujukan untuk project Supabase baru/kosong.
- Status draft proyek pada UI disimpan sebagai `pending` di enum Supabase.
- Checkout dan admin pesanan menggunakan `orders` serta `order_items` dari skema utama. `03_purchase_orders_simulation.sql` sudah ditandai deprecated.
- Pembayaran tetap simulasi, bukan pemrosesan uang sungguhan.
