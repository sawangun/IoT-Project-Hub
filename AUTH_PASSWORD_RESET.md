# Catatan keamanan autentikasi dan reset kata sandi

## Fitur yang direvisi
- Login dan registrasi tetap menggunakan Supabase Auth; kata sandi tidak disimpan pada tabel `profiles`.
- Halaman **Lupa kata sandi** meminta Supabase mengirim email pemulihan.
- Halaman **Buat kata sandi baru** membaca token recovery dari URL fragment dan mengirim perubahan kata sandi langsung ke Supabase Auth. Token tidak dikirim ke server Laravel dan dihapus dari address bar setelah dibaca.
- Permintaan email pemulihan dibatasi dengan middleware `throttle:5,1`, dan pesan publik tidak mengungkap apakah email terdaftar.
- Kegagalan login tidak lagi mengonfirmasi akun secara otomatis. Detail teknis autentikasi juga tidak ditampilkan kepada pengguna.

## Konfigurasi Supabase yang wajib
1. Pada `.env`, pastikan `SUPABASE_URL`, `SUPABASE_ANON_KEY`, dan `APP_URL` sesuai lingkungan yang sedang dipakai.
2. Buka **Supabase Dashboard → Authentication → URL Configuration**.
3. Tambahkan URL pemulihan yang tepat ke **Redirect URLs**, misalnya `http://127.0.0.1:8000/reset-password` untuk pengembangan lokal. Untuk deployment, tambahkan URL HTTPS domain produksi yang sebenarnya. Jangan menggunakan wildcard yang terlalu luas.
4. Pastikan SMTP/email provider Supabase aktif dan template email **Reset Password** mengarah ke URL pemulihan Supabase standar (umumnya `{{ .ConfirmationURL }}`), agar Supabase memverifikasi token sebelum mengarahkan pengguna kembali ke aplikasi.
5. Atur masa berlaku token email di **Supabase Dashboard → Authentication → Providers/Sign In → Email** (nama menu dapat sedikit berbeda), pada pengaturan **Email OTP Expiration**. Gunakan `3600` detik untuk 60 menit, atau pilih durasi lain sesuai kebutuhan. Supabase yang menegakkan masa berlaku token; variabel aplikasi tidak dapat mengubah pengaturan tersebut dari sisi Laravel.
6. Atur `SUPABASE_RECOVERY_LINK_MINUTES=60` pada `.env` agar keterangan durasi di halaman cocok dengan pengaturan Supabase. Jika mengubah durasi di Dashboard, ubah variabel ini juga dan jalankan `php artisan config:clear`.
7. Uji end-to-end menggunakan akun uji: minta email reset, buka tautan terbaru, ubah kata sandi, lalu login dengan kata sandi baru. Pastikan tautan kedaluwarsa atau dipakai ulang ditolak.

## Catatan keamanan produksi
- Jangan pernah memasukkan `SUPABASE_SERVICE_ROLE_KEY` ke JavaScript, Blade yang dikirim ke browser, atau variabel `VITE_*`.
- `SUPABASE_AUTO_CONFIRM=true` hanya untuk pengembangan lokal. Nonaktifkan untuk produksi dan jangan aktifkan auto-confirm email jika kebijakan kampus mewajibkan verifikasi email.
- Gunakan HTTPS di produksi, `APP_DEBUG=false`, `APP_ENV=production`, dan rahasia `.env` yang kuat serta tidak masuk Git.
- Tautan reset hanya akan bekerja jika URL redirect diizinkan oleh konfigurasi Supabase dan email recovery berhasil dikirim. Masa berlaku yang ditampilkan di aplikasi hanya keterangan; masa berlaku sebenarnya harus disetel di Supabase Dashboard.
