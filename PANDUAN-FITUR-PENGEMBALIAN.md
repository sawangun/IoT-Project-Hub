# Fitur Form Pengembalian & Inspeksi Hardware

## Fitur
- Pengguna membuka `Form Pengembalian` dari pinjaman berstatus `active` atau `overdue`.
- Foto bukti diambil melalui kamera browser (`getUserMedia`). Form tidak menyediakan pemilih foto/galeri.
- Timestamp lokal dibakar ke dalam foto saat tombol Ambil Foto ditekan. Server juga menyimpan timestamp penerimaan pada `return_photo_taken_at`.
- Empat pemeriksaan penataan locker wajib dicentang.
- Kondisi `baik` mengembalikan unit dan locker ke status `available`.
- Kondisi `perlu_perbaikan` atau `rusak` menandai unit dan locker sebagai `maintenance` agar tidak langsung dipinjam lagi.
- Admin dapat membuka foto bukti dan timestamp dari daftar Manajemen Peminjaman.

## 1. Tambahkan kolom Supabase (wajib, sekali saja)
Buka Supabase Dashboard → SQL Editor → New query, lalu jalankan isi file:
`database/supabase/04_return_evidence_columns.sql`

SQL tersebut hanya menambah kolom dan tidak menghapus atau mengubah data lama.

## 2. Siapkan penyimpanan foto Laravel
Dari folder utama proyek, jalankan:

```bash
php artisan storage:link
```

Pastikan folder `storage/app/public` dapat ditulis oleh PHP/web server dan penyimpanan disk server cukup. Foto disimpan di `storage/app/public/return-evidence/`.

## 3. Kamera browser
Akses aplikasi melalui HTTPS (atau localhost saat pengembangan) dan izinkan akses kamera. Halaman tidak menampilkan tombol pilih foto dari galeri. Timestamp yang tercetak pada gambar berasal dari jam perangkat; `return_photo_taken_at` di database adalah waktu server saat bukti diterima.

## Catatan
Pembatasan kamera diterapkan pada antarmuka browser dan validasi request. Browser web tidak menyediakan bukti kriptografis yang sepenuhnya menjamin sebuah gambar berasal dari sensor kamera; ini mencegah pemilihan galeri melalui alur normal halaman, bukan anti-manipulasi forensik.
