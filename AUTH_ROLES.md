# Autentikasi 3 Role

- **Guest**: pengunjung yang belum login. Bisa membuka halaman publik, login, dan registrasi.
- **Pengguna**: akun hasil registrasi. Bisa membuka area pengguna (`/dashboard`, project, katalog, peminjaman, transaksi, checkout).
- **Admin**: hanya bisa membuka seluruh route `/admin/*`.

## Instalasi / update database

```bash
composer install
php artisan migrate
php artisan db:seed
php artisan serve
```

Akun demo development setelah `db:seed`:
- Admin: `admin@iothub.test` / `Admin12345!`
- Pengguna: `pengguna@iothub.test` / `Pengguna12345!`

Registrasi publik selalu membuat role `pengguna`; role admin tidak dapat dipilih dari form registrasi.
