# Admin Inventory — Supabase

Halaman inventaris menggunakan tabel Supabase, bukan data session.

- `/admin/lockers` — membaca dan memperbarui `public.lockers`.
- `/admin/kits-components` — membaca `public.kits`, `public.kit_items`, dan `public.components`.
- `/admin/kits-components/kits/create` — membuat kit dan relasi komponen.
- `/admin/kits-components/components/create` — menambah komponen dan stok.

Metadata tambahan (`code`, `category`, `unit`, `location`, `description` pada komponen; `code`, `category`, `description` pada kit; `location` dan `item` pada locker) disediakan oleh migration Laravel metadata. Stok kit dan komponen dibaca dari kolom `stock`.
