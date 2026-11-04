-- Jalankan sekali di Supabase SQL Editor pada database yang sudah berisi tabel borrowings.
-- Aman untuk data lama: hanya menambahkan kolom jika belum tersedia.
ALTER TABLE public.borrowings
    ADD COLUMN IF NOT EXISTS return_photo_path text,
    ADD COLUMN IF NOT EXISTS return_photo_taken_at timestamptz,
    ADD COLUMN IF NOT EXISTS return_condition public.unit_condition,
    ADD COLUMN IF NOT EXISTS return_checklist jsonb,
    ADD COLUMN IF NOT EXISTS return_notes text;
