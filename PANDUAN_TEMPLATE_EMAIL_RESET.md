# Memasang template email reset password yang sudah didesain

Email reset password pada project ini dikirim oleh **Supabase Auth**, bukan oleh Laravel. Karena itu, file CSS Laravel tidak akan mengubah tampilan email yang diterima pengguna. Template email perlu ditempelkan di dashboard Supabase.

1. Buka file `SUPABASE_PASSWORD_RESET_EMAIL_TEMPLATE.html`.
2. Salin seluruh isi HTML.
3. Buka Supabase Dashboard untuk project yang digunakan aplikasi.
4. Masuk ke **Authentication → Email Templates → Reset Password** (nama menu dapat sedikit berbeda).
5. Tempel HTML ke bagian **Body** template. Subject yang disarankan: `Atur Ulang Kata Sandi | IoT Project Hub`.
6. Simpan perubahan dan kirim email uji.
7. Pastikan pengaturan masa berlaku recovery token di Supabase sesuai dengan teks template, yaitu 60 menit. Nilai di email hanya teks informasi; masa berlaku sebenarnya tetap dikendalikan oleh konfigurasi Supabase.

Template menggunakan variabel Supabase `{{ .ConfirmationURL }}` untuk tautan pemulihan. Jangan menggantinya dengan URL contoh atau menghapus variabel tersebut.
