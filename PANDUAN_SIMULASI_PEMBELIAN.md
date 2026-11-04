# Panduan Simulasi Pembelian

Fitur ini merupakan simulasi demo. Tidak ada integrasi payment gateway atau uang sungguhan.

## Database
Checkout menggunakan tabel bawaan skema Supabase `orders` dan `order_items`; tidak membutuhkan tabel tambahan `purchase_orders`. Metode pembayaran mengikuti enum `payment_method`: `qris` atau `cod`.

## Alur
1. Login sebagai pengguna yang profil Supabase-nya sudah terverifikasi.
2. Pilih kit atau komponen dari katalog.
3. Isi checkout dan pilih QRIS atau bayar saat pengambilan (COD).
4. Pesanan dibuat di `orders`, rincian di `order_items`, dan stok dikurangi dalam transaksi database.
5. Pembayaran dapat disimulasikan pada halaman pembayaran. Admin mengubah status pemenuhan dari `/admin/purchase-orders`.
6. Jika pesanan dibatalkan, trigger `on_order_status_change` mengembalikan stok.

`paid` hanya berarti simulasi pembayaran berhasil, bukan bukti uang sungguhan diterima.
