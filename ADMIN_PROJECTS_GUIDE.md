# Admin Projects pages

Halaman manajemen proyek admin ditambahkan dengan mengikuti dua referensi desain yang disertakan.

## Routes
- `/admin/projects` — daftar, cari, dan filter proyek
- `/admin/projects/create` — tambah proyek baru
- `/admin/projects/{id}/edit` — edit proyek

## Yang sudah bisa dilakukan
- Menampilkan kartu proyek dengan status draft/published
- Cari proyek berdasarkan judul, tim, atau hardware
- Filter berdasarkan kategori dan status
- Tambah proyek, edit informasi, hapus proyek
- Simpan sebagai draft atau publikasikan
- Validasi input server-side
- Layout responsif mengikuti sidebar admin yang sudah ada

## Penyimpanan
Fitur ini belum memakai tabel/model database khusus untuk proyek. Data contoh dan perubahan disimpan dalam session Laravel (`admin_projects`) agar form dan aksi CRUD dapat didemonstrasikan tanpa migration proyek. Perilaku persistensi mengikuti `SESSION_DRIVER` yang dipakai aplikasi; jika memakai session file, data bersifat sementara dan dapat hilang saat session berakhir.

Gambar pada form saat ini menerima URL opsional, bukan unggah file. Autentikasi/otorisasi admin juga belum ditambahkan di route karena sistem login proyek saat ini masih berupa frontend sementara.
