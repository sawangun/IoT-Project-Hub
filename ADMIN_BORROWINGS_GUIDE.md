# Admin Borrowings

Data manajemen peminjaman dibaca dan ditulis langsung ke Supabase PostgreSQL (`borrowings`, `profiles`, `hardware_units`, `projects`, `lockers`, `qr_tokens`).

## Routes
- `/admin/borrowings` — daftar, pencarian, filter, dan status peminjaman
- `/admin/borrowings/create` — buat pengajuan dari akun, unit hardware, dan locker yang benar-benar ada di database
- `/admin/borrowing-approval` — setujui/tolak pengajuan; persetujuan menerbitkan token QR

Peminjaman baru selalu berstatus `pending`. Status mengikuti enum `borrowing_status` dari SQL utama. Kolom `notes` ditambahkan oleh migration metadata.
