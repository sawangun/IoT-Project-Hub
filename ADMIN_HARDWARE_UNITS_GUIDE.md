# Admin Hardware Units

Menu baru: **Admin > Hardware Units** (`/admin/hardware-units`).

Halaman ini mengelola baris fisik pada tabel Supabase `public.hardware_units`, bukan stok komponen atau kit.

Kolom yang digunakan mengikuti `database/supabase/01_schema.sql`:
- `project_id` (relasi ke `projects.id`)
- `serial_code` (unik)
- `condition`: `baik`, `perlu_perbaikan`, `rusak`
- `status`: `available`, `borrowed`, `maintenance`

Pengguna hanya melihat unit dengan `hardware_units.status = 'available'` yang terkait ke proyek `projects.status = 'published'`.

Tidak ada migrasi atau perubahan struktur database yang diperlukan jika tabel `public.hardware_units` sudah dibuat sesuai schema proyek. Pastikan proyek terkait sudah berstatus `published`, lalu tambahkan unit dengan status `available` dari menu Hardware Units.
