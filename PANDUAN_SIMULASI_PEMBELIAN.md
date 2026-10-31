# Panduan Simulasi Pembelian

Fitur ini merupakan simulasi demo. Tidak ada integrasi payment gateway, QRIS aktif, transfer bank, atau uang sungguhan.

## 1. Siapkan tabel transaksi di Supabase

Buka Supabase Dashboard → SQL Editor, lalu jalankan isi file:

`database/supabase/03_purchase_orders_simulation.sql`

File tersebut membuat tabel `purchase_orders` yang dipakai oleh halaman checkout, simulasi pembayaran, riwayat pengguna, dan daftar pesanan admin.

## 2. Alur pengguna

1. Login sebagai pengguna.
2. Buka katalog Kit & Komponen dan pilih item.
3. Isi checkout lalu pilih QRIS, EduPay, atau Virtual Account (semuanya simulasi).
4. Klik **Buat Pesanan**.
5. Pada halaman simulasi, klik **Simulasikan Pembayaran Berhasil**.
6. Status menjadi `paid` (Lunas simulasi), stok item dikurangi, dan pesanan muncul di Riwayat Transaksi.

Pesanan yang belum disimulasikan lunas tetap berstatus `pending` dan tidak mengurangi stok.

## 3. Alur admin

Login sebagai admin dan buka `/admin/purchase-orders`. Admin dapat mengubah pesanan lunas menjadi Diproses, Selesai, atau Dibatalkan. Jika pesanan dibatalkan, stok dikembalikan.

## Catatan penting

- `paid` berarti pembayaran simulasi berhasil, bukan bukti uang diterima.
- QRIS/VA yang terlihat hanya keterangan ilustrasi; tidak ada QR atau nomor rekening aktif.
- Pastikan tabel `kits`, `components`, dan `profiles` yang sudah dipakai aplikasi tersedia di Supabase.
- Inventaris admin pada versi project ini masih menggunakan session demo, sedangkan katalog pembelian membaca tabel database. Pastikan data item katalog memang tersedia di tabel database.
