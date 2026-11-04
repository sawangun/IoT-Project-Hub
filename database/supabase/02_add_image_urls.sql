-- Jalankan di Supabase SQL Editor setelah schema utama tersedia.
-- Tambahan opsional untuk fitur foto yang dibuat pada versi sebelumnya.
alter table public.projects add column if not exists image_url text;
alter table public.components add column if not exists image_url text;
alter table public.kits add column if not exists image_url text;
