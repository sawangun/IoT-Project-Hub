-- Jalankan sekali pada project Supabase yang sudah memiliki tabel borrowings.
-- Menambahkan detail yang diisi pada Form Pengajuan Peminjaman.
alter table public.borrowings
  add column if not exists purpose text,
  add column if not exists activity_type text;
