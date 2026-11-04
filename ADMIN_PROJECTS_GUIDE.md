# Admin Projects

CRUD proyek admin tersimpan di Supabase PostgreSQL (`public.projects`).

## Routes
- `/admin/projects` — daftar, cari, dan filter proyek
- `/admin/projects/create` — tambah proyek
- `/admin/projects/{id}/edit` — edit proyek

Status draft pada antarmuka dipetakan ke status database `pending`; publikasi memakai `published`. Tingkat kesulitan dipetakan ke enum `pemula`, `menengah`, dan `mahir`.

Kolom tambahan untuk metadata tampilan (`category`, `team`, `year`, `hardware`, `locker`, `image_url`) ditambahkan oleh migration Laravel `2026_11_01_000001_add_admin_metadata_to_supabase_tables.php`.
