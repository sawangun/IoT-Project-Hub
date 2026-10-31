-- =====================================================================
-- IoT Project Hub (Sawangun Digital) - Skema database Supabase
-- Cara pakai: Supabase Dashboard > SQL Editor > New query > tempel > Run
-- Untuk project Supabase BARU/kosong (script ini tidak idempotent).
--
-- Isi:
--   0. (opsional) reset
--   1. Enum
--   2. Tabel (17 tabel sesuai ERD) + index
--   3. Fungsi bantu + trigger
--   4. Fungsi RPC (approve peminjaman, verifikasi QR, buat pesanan, dst.)
--   5. Row Level Security (RLS) + policy
--   6. Data awal (24 locker) + cara membuat admin pertama
--
-- Catatan desain:
--   * Tabel USERS pada ERD = public.profiles. Email & kata sandi disimpan
--     oleh Supabase Auth (auth.users); profiles terisi otomatis lewat trigger.
--   * Verifikasi email sebaiknya memakai OTP bawaan Supabase Auth.
--     Tabel email_verifications disediakan sesuai ERD bila ingin aturan kunci
--     sendiri (maks 5 percobaan, kunci 15 menit). Simpan HASH kode, bukan kode
--     asli, dan akses hanya lewat service role / Edge Function.
--   * Pesanan dibuat lewat fungsi create_order() agar harga diambil dari
--     database dan stok berkurang atomik (klien tidak boleh menulis harga).
-- =====================================================================

-- ---------------------------------------------------------------------
-- 0. RESET (hapus tanda komentar HANYA jika ingin mengulang dari nol)
-- ---------------------------------------------------------------------
-- drop trigger if exists on_auth_user_created on auth.users;
-- drop trigger if exists on_auth_user_updated on auth.users;
-- drop schema public cascade; create schema public;
-- grant usage on schema public to postgres, anon, authenticated, service_role;
-- grant all on schema public to postgres, service_role;

-- ---------------------------------------------------------------------
-- 1. ENUM
-- ---------------------------------------------------------------------
create type public.user_role            as enum ('mahasiswa', 'umum', 'admin');
create type public.project_status       as enum ('pending', 'published', 'rejected', 'archived');
create type public.difficulty_level     as enum ('pemula', 'menengah', 'mahir');
create type public.unit_status          as enum ('available', 'borrowed', 'maintenance');
create type public.unit_condition       as enum ('baik', 'perlu_perbaikan', 'rusak');
create type public.locker_status        as enum ('available', 'occupied', 'maintenance', 'offline');
create type public.borrowing_status     as enum ('pending', 'approved', 'active', 'overdue', 'returned', 'rejected', 'cancelled');
create type public.qr_status            as enum ('active', 'used', 'expired', 'revoked');
create type public.payment_method       as enum ('qris', 'cod');
create type public.order_status         as enum ('pending', 'paid', 'processing', 'ready_pickup', 'completed', 'cancelled');
create type public.notification_type    as enum ('borrowing_status', 'return_reminder', 'project_status', 'order_status', 'system');
create type public.notification_channel as enum ('in_app', 'whatsapp', 'email', 'telegram', 'webhook');

-- ---------------------------------------------------------------------
-- 2. TABEL
-- ---------------------------------------------------------------------

-- 2.1 USERS -> profiles (1:1 dengan auth.users)
create table public.profiles (
  id          uuid primary key references auth.users (id) on delete cascade,
  name        text        not null,
  email       text        unique,
  role        public.user_role not null default 'mahasiswa',
  nim         text,
  phone       text,
  is_verified boolean     not null default false,
  created_at  timestamptz not null default now(),
  updated_at  timestamptz not null default now()
);

-- 2.2 EMAIL_VERIFICATIONS (opsional, hanya via service role)
create table public.email_verifications (
  id           bigint generated always as identity primary key,
  user_id      uuid        not null references public.profiles (id) on delete cascade,
  code         text        not null,           -- simpan hash, bukan kode asli
  expires_at   timestamptz not null,
  attempts     int         not null default 0 check (attempts >= 0),
  locked_until timestamptz,
  created_at   timestamptz not null default now()
);

-- 2.3 PROJECTS
create table public.projects (
  id            bigint generated always as identity primary key,
  creator_id    uuid references public.profiles (id) on delete set null,
  reviewed_by   uuid references public.profiles (id) on delete set null,
  title         text not null,
  description   text,
  difficulty    public.difficulty_level not null default 'pemula',
  schematic_url text,
  repo_url      text,
  status        public.project_status not null default 'pending',
  created_at    timestamptz not null default now(),
  updated_at    timestamptz not null default now()
);

-- 2.4 SAVED_PROJECTS
create table public.saved_projects (
  user_id    uuid   not null references public.profiles (id) on delete cascade,
  project_id bigint not null references public.projects (id) on delete cascade,
  saved_at   timestamptz not null default now(),
  primary key (user_id, project_id)
);

-- 2.5 COMPONENTS
create table public.components (
  id              bigint generated always as identity primary key,
  name            text not null,
  spec            text,
  price           numeric(12,2) not null default 0 check (price >= 0),
  stock           int  not null default 0 check (stock >= 0),
  low_stock_limit int  not null default 5 check (low_stock_limit >= 0),
  shopee_url      text,
  created_at      timestamptz not null default now(),
  updated_at      timestamptz not null default now()
);

-- 2.6 PROJECT_COMPONENTS (BOM / daftar komponen proyek)
create table public.project_components (
  project_id   bigint not null references public.projects (id)   on delete cascade,
  component_id bigint not null references public.components (id) on delete restrict,
  qty          int    not null default 1 check (qty > 0),
  primary key (project_id, component_id)
);

-- 2.7 KITS (kit paketan)
create table public.kits (
  id         bigint generated always as identity primary key,
  project_id bigint not null references public.projects (id) on delete cascade,
  name       text   not null,
  price      numeric(12,2) not null default 0 check (price >= 0),
  stock      int    not null default 0 check (stock >= 0),
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

-- 2.8 KIT_ITEMS
create table public.kit_items (
  kit_id       bigint not null references public.kits (id)       on delete cascade,
  component_id bigint not null references public.components (id) on delete restrict,
  qty          int    not null default 1 check (qty > 0),
  primary key (kit_id, component_id)
);

-- 2.9 HARDWARE_UNITS (unit fisik yang dipinjam)
create table public.hardware_units (
  id          bigint generated always as identity primary key,
  project_id  bigint not null references public.projects (id) on delete cascade,
  serial_code text   not null unique,
  condition   public.unit_condition not null default 'baik',
  status      public.unit_status    not null default 'available'
);

-- 2.10 LOCKERS
create table public.lockers (
  id            bigint generated always as identity primary key,
  code          text not null unique,
  status        public.locker_status not null default 'available',
  sensor_status text not null default 'online' check (sensor_status in ('online', 'offline', 'error'))
);

-- 2.11 BORROWINGS
create table public.borrowings (
  id           bigint generated always as identity primary key,
  user_id      uuid   not null references public.profiles (id) on delete restrict,
  unit_id      bigint not null references public.hardware_units (id) on delete restrict,
  locker_id    bigint references public.lockers (id) on delete set null,
  approved_by  uuid   references public.profiles (id) on delete set null,
  status       public.borrowing_status not null default 'pending',
  start_date   date   not null default current_date,
  due_date     date   not null,
  purpose      text,
  activity_type text,
  picked_up_at timestamptz,
  returned_at  timestamptz,
  created_at   timestamptz not null default now(),
  updated_at   timestamptz not null default now(),
  constraint borrowings_due_after_start check (due_date >= start_date),
  constraint borrowings_return_after_pickup check (returned_at is null or picked_up_at is null or returned_at >= picked_up_at)
);

-- Satu unit tidak boleh dipinjam dua orang pada saat yang sama
create unique index borrowings_one_open_per_unit
  on public.borrowings (unit_id)
  where status in ('approved', 'active', 'overdue');

-- 2.12 QR_TOKENS
create table public.qr_tokens (
  id           bigint generated always as identity primary key,
  borrowing_id bigint not null references public.borrowings (id) on delete cascade,
  token        text   not null unique,
  expires_at   timestamptz not null,
  status       public.qr_status not null default 'active',
  created_at   timestamptz not null default now()
);

-- 2.13 ORDERS
create table public.orders (
  id             bigint generated always as identity primary key,
  user_id        uuid not null references public.profiles (id) on delete restrict,
  total          numeric(12,2) not null default 0 check (total >= 0),
  payment_method public.payment_method not null,
  status         public.order_status   not null default 'pending',
  created_at     timestamptz not null default now(),
  updated_at     timestamptz not null default now()
);

-- 2.14 ORDER_ITEMS (kit ATAU komponen eceran)
create table public.order_items (
  id           bigint generated always as identity primary key,
  order_id     bigint not null references public.orders (id) on delete cascade,
  kit_id       bigint references public.kits (id)       on delete restrict,
  component_id bigint references public.components (id) on delete restrict,
  qty          int    not null check (qty > 0),
  unit_price   numeric(12,2) not null check (unit_price >= 0),
  constraint order_items_kit_xor_component
    check (((kit_id is not null)::int + (component_id is not null)::int) = 1)
);

-- 2.15 NOTIFICATIONS
create table public.notifications (
  id         bigint generated always as identity primary key,
  user_id    uuid not null references public.profiles (id) on delete cascade,
  type       public.notification_type not null default 'system',
  message    text not null,
  is_read    boolean not null default false,
  created_at timestamptz not null default now()
);

-- 2.16 NOTIFICATION_PREFERENCES
create table public.notification_preferences (
  user_id uuid not null references public.profiles (id) on delete cascade,
  channel public.notification_channel not null,
  enabled boolean not null default true,
  primary key (user_id, channel)
);

-- 2.17 ACTIVITY_LOGS
create table public.activity_logs (
  id         bigint generated always as identity primary key,
  user_id    uuid   references public.profiles (id) on delete set null,
  locker_id  bigint references public.lockers (id)  on delete set null,
  action     text   not null,
  created_at timestamptz not null default now()
);

-- Index untuk foreign key & pencarian umum
create index on public.email_verifications (user_id);
create index on public.projects (creator_id);
create index on public.projects (reviewed_by);
create index on public.projects (status, created_at desc);
create index on public.saved_projects (project_id);
create index on public.project_components (component_id);
create index on public.kits (project_id);
create index on public.kit_items (component_id);
create index on public.hardware_units (project_id);
create index on public.borrowings (user_id, created_at desc);
create index on public.borrowings (unit_id);
create index on public.borrowings (locker_id);
create index on public.borrowings (approved_by);
create index on public.borrowings (status, due_date);
create index on public.qr_tokens (borrowing_id);
create index on public.orders (user_id, created_at desc);
create index on public.order_items (order_id);
create index on public.order_items (kit_id);
create index on public.order_items (component_id);
create index on public.notifications (user_id, is_read, created_at desc);
create index on public.activity_logs (user_id);
create index on public.activity_logs (locker_id);
create index on public.activity_logs (created_at desc);

-- ---------------------------------------------------------------------
-- 3. FUNGSI BANTU + TRIGGER
-- ---------------------------------------------------------------------

-- 3.1 updated_at otomatis
create or replace function public.set_updated_at()
returns trigger language plpgsql
set search_path = ''
as $$
begin
  new.updated_at := now();
  return new;
end;
$$;

create trigger trg_profiles_updated   before update on public.profiles  for each row execute function public.set_updated_at();
create trigger trg_projects_updated   before update on public.projects  for each row execute function public.set_updated_at();
create trigger trg_components_updated before update on public.components for each row execute function public.set_updated_at();
create trigger trg_kits_updated       before update on public.kits       for each row execute function public.set_updated_at();
create trigger trg_borrowings_updated before update on public.borrowings for each row execute function public.set_updated_at();
create trigger trg_orders_updated     before update on public.orders     for each row execute function public.set_updated_at();

-- 3.2 Cek admin (dipakai di policy RLS)
create or replace function public.is_admin()
returns boolean
language sql stable security definer
set search_path = ''
as $$
  select exists (
    select 1 from public.profiles
    where id = (select auth.uid()) and role = 'admin'
  );
$$;

-- 3.3 Profil otomatis saat pengguna mendaftar di Supabase Auth
-- Metadata pendaftaran (opsional): { name, role: 'mahasiswa' | 'umum', nim, phone }
-- Peran 'admin' TIDAK bisa dipilih lewat pendaftaran.
create or replace function public.handle_new_user()
returns trigger
language plpgsql security definer
set search_path = ''
as $$
declare
  v_role public.user_role;
begin
  v_role := case
              when new.raw_user_meta_data ->> 'role' = 'umum' then 'umum'::public.user_role
              else 'mahasiswa'::public.user_role
            end;

  insert into public.profiles (id, name, email, role, nim, phone, is_verified)
  values (
    new.id,
    coalesce(nullif(new.raw_user_meta_data ->> 'name', ''),
             nullif(split_part(coalesce(new.email, ''), '@', 1), ''),
             'Pengguna'),
    new.email,
    v_role,
    nullif(new.raw_user_meta_data ->> 'nim', ''),
    nullif(new.raw_user_meta_data ->> 'phone', ''),
    new.email_confirmed_at is not null
  );

  insert into public.notification_preferences (user_id, channel, enabled)
  values (new.id, 'in_app', true);

  return new;
end;
$$;

create trigger on_auth_user_created
  after insert on auth.users
  for each row execute function public.handle_new_user();

-- 3.4 Sinkron email & status verifikasi dari Supabase Auth
create or replace function public.sync_profile_from_auth()
returns trigger
language plpgsql security definer
set search_path = ''
as $$
begin
  update public.profiles
     set email       = new.email,
         is_verified = (new.email_confirmed_at is not null)
   where id = new.id;
  return new;
end;
$$;

create trigger on_auth_user_updated
  after update of email, email_confirmed_at on auth.users
  for each row execute function public.sync_profile_from_auth();

-- 3.5 Cegah pengguna biasa mengubah role / is_verified / email lewat API
create or replace function public.guard_profile_changes()
returns trigger
language plpgsql
set search_path = ''
as $$
begin
  if (select auth.uid()) is not null and not public.is_admin() then
    if new.role        is distinct from old.role
       or new.is_verified is distinct from old.is_verified
       or new.email       is distinct from old.email
       or new.id          is distinct from old.id then
      raise exception 'Kolom role, is_verified, dan email tidak boleh diubah langsung'
        using errcode = '42501';
    end if;
  end if;
  return new;
end;
$$;

create trigger trg_profiles_guard
  before update on public.profiles
  for each row execute function public.guard_profile_changes();

-- 3.6 Validasi sebelum pengajuan peminjaman dibuat
create or replace function public.borrowings_before_insert()
returns trigger
language plpgsql
set search_path = ''
as $$
declare
  v_unit_status public.unit_status;
begin
  select status into v_unit_status from public.hardware_units where id = new.unit_id;
  if v_unit_status is null then
    raise exception 'Unit perangkat tidak ditemukan';
  end if;
  if v_unit_status = 'maintenance' then
    raise exception 'Unit perangkat sedang dalam perawatan';
  end if;
  return new;
end;
$$;

create trigger trg_borrowings_before_insert
  before insert on public.borrowings
  for each row execute function public.borrowings_before_insert();

-- 3.7 Sinkron status unit & locker, catat log, kirim notifikasi saat status peminjaman berubah
create or replace function public.on_borrowing_status_change()
returns trigger
language plpgsql security definer
set search_path = ''
as $$
declare
  v_msg     text;
  v_changed boolean;
begin
  -- Unit & locker terkunci selama peminjaman berjalan
  if new.status in ('approved', 'active', 'overdue') then
    update public.hardware_units set status = 'borrowed'
     where id = new.unit_id and status <> 'maintenance';
    if new.locker_id is not null then
      update public.lockers set status = 'occupied'
       where id = new.locker_id and status = 'available';
    end if;
  elsif new.status in ('returned', 'rejected', 'cancelled') then
    update public.hardware_units set status = 'available'
     where id = new.unit_id and status = 'borrowed';
    if new.locker_id is not null then
      update public.lockers set status = 'available'
       where id = new.locker_id and status = 'occupied';
    end if;
    update public.qr_tokens set status = 'revoked'
     where borrowing_id = new.id and status = 'active';
  end if;

  -- Log aktivitas + notifikasi hanya jika status benar-benar berubah
  if tg_op = 'UPDATE' then
    v_changed := (old.status is distinct from new.status);
  else
    v_changed := true;
  end if;

  if v_changed then
    insert into public.activity_logs (user_id, locker_id, action)
    values ((select auth.uid()), new.locker_id,
            'peminjaman #' || new.id || ' -> ' || new.status::text);

    v_msg := case new.status
      when 'pending'   then 'Pengajuan peminjaman #' || new.id || ' diterima dan menunggu persetujuan admin.'
      when 'approved'  then 'Pengajuan peminjaman #' || new.id || ' disetujui. Gunakan QR untuk mengambil perangkat.'
      when 'active'    then 'Perangkat peminjaman #' || new.id || ' sudah diambil. Jangan lupa kembalikan tepat waktu.'
      when 'overdue'   then 'Peminjaman #' || new.id || ' melewati batas pengembalian. Segera kembalikan perangkat.'
      when 'returned'  then 'Peminjaman #' || new.id || ' selesai. Terima kasih sudah mengembalikan perangkat.'
      when 'rejected'  then 'Pengajuan peminjaman #' || new.id || ' ditolak admin.'
      when 'cancelled' then 'Pengajuan peminjaman #' || new.id || ' dibatalkan.'
    end;

    insert into public.notifications (user_id, type, message)
    values (new.user_id, 'borrowing_status', v_msg);
  end if;

  return new;
end;
$$;

create trigger trg_borrowings_after_change
  after insert or update of status on public.borrowings
  for each row execute function public.on_borrowing_status_change();

-- 3.8 Notifikasi ke pembuat proyek saat status proyek berubah
create or replace function public.on_project_status_change()
returns trigger
language plpgsql security definer
set search_path = ''
as $$
begin
  if old.status is distinct from new.status and new.creator_id is not null then
    insert into public.notifications (user_id, type, message)
    values (
      new.creator_id, 'project_status',
      case new.status
        when 'published' then 'Proyek "' || new.title || '" disetujui dan sudah tampil di katalog.'
        when 'rejected'  then 'Proyek "' || new.title || '" belum disetujui. Silakan perbaiki dan ajukan kembali.'
        when 'pending'   then 'Proyek "' || new.title || '" menunggu persetujuan admin.'
        else 'Status proyek "' || new.title || '" berubah menjadi ' || new.status::text || '.'
      end
    );
  end if;
  return new;
end;
$$;

create trigger trg_projects_status_change
  after update of status on public.projects
  for each row execute function public.on_project_status_change();

-- 3.9 Pesanan: kembalikan stok saat dibatalkan + notifikasi status
create or replace function public.on_order_status_change()
returns trigger
language plpgsql security definer
set search_path = ''
as $$
begin
  if old.status is distinct from new.status then
    if new.status = 'cancelled' and old.status <> 'cancelled' then
      update public.kits k
         set stock = k.stock + s.q
        from (select kit_id, sum(qty)::int as q
                from public.order_items
               where order_id = new.id and kit_id is not null
               group by kit_id) s
       where k.id = s.kit_id;

      update public.components c
         set stock = c.stock + s.q
        from (select component_id, sum(qty)::int as q
                from public.order_items
               where order_id = new.id and component_id is not null
               group by component_id) s
       where c.id = s.component_id;
    end if;

    insert into public.notifications (user_id, type, message)
    values (
      new.user_id, 'order_status',
      case new.status
        when 'pending'      then 'Pesanan #' || new.id || ' dibuat dan menunggu pembayaran.'
        when 'paid'         then 'Pembayaran pesanan #' || new.id || ' diterima.'
        when 'processing'   then 'Pesanan #' || new.id || ' sedang disiapkan.'
        when 'ready_pickup' then 'Pesanan #' || new.id || ' siap diambil di kampus.'
        when 'completed'    then 'Pesanan #' || new.id || ' selesai.'
        when 'cancelled'    then 'Pesanan #' || new.id || ' dibatalkan.'
      end
    );
  end if;
  return new;
end;
$$;

create trigger trg_orders_status_change
  after update of status on public.orders
  for each row execute function public.on_order_status_change();

-- ---------------------------------------------------------------------
-- 4. FUNGSI RPC (dipanggil dari aplikasi: supabase.rpc('nama_fungsi', {...}))
-- ---------------------------------------------------------------------

-- 4.1 Admin menyetujui peminjaman -> membuat token QR, mengembalikan token
create or replace function public.approve_borrowing(
  p_borrowing_id bigint,
  p_locker_id    bigint default null,
  p_valid_hours  int    default 24
)
returns text
language plpgsql security definer
set search_path = public
as $$
declare
  v_token text;
begin
  if not public.is_admin() then
    raise exception 'Hanya admin yang dapat menyetujui peminjaman' using errcode = '42501';
  end if;

  perform 1 from public.borrowings where id = p_borrowing_id and status = 'pending' for update;
  if not found then
    raise exception 'Pengajuan tidak ditemukan atau bukan berstatus pending';
  end if;

  if p_locker_id is not null then
    perform 1 from public.lockers where id = p_locker_id and status = 'available' for update;
    if not found then
      raise exception 'Locker tidak tersedia';
    end if;
  end if;

  update public.borrowings
     set status      = 'approved',
         approved_by = (select auth.uid()),
         locker_id   = coalesce(p_locker_id, locker_id)
   where id = p_borrowing_id;

  v_token := replace(gen_random_uuid()::text, '-', '') || replace(gen_random_uuid()::text, '-', '');

  insert into public.qr_tokens (borrowing_id, token, expires_at)
  values (p_borrowing_id, v_token, now() + make_interval(hours => p_valid_hours));

  return v_token;
end;
$$;

-- 4.2 Verifikasi QR di smart locker -> peminjaman jadi 'active'
-- Boleh dipanggil admin atau service role (perangkat locker). Mengembalikan id peminjaman,
-- atau NULL jika token tidak valid / sudah dipakai / kedaluwarsa.
create or replace function public.verify_qr_pickup(p_token text)
returns bigint
language plpgsql security definer
set search_path = public
as $$
declare
  v_qr public.qr_tokens;
begin
  if not ((select auth.role()) = 'service_role' or public.is_admin()) then
    raise exception 'Tidak diizinkan' using errcode = '42501';
  end if;

  select * into v_qr from public.qr_tokens
   where token = p_token and status = 'active'
   for update;
  if not found then
    return null;
  end if;

  if v_qr.expires_at < now() then
    update public.qr_tokens set status = 'expired' where id = v_qr.id;
    return null;
  end if;

  update public.qr_tokens set status = 'used' where id = v_qr.id;
  update public.borrowings
     set status = 'active', picked_up_at = now()
   where id = v_qr.borrowing_id and status = 'approved';

  return v_qr.borrowing_id;
end;
$$;

-- 4.3 Pengembalian perangkat (admin / service role)
create or replace function public.return_borrowing(p_borrowing_id bigint)
returns void
language plpgsql security definer
set search_path = public
as $$
begin
  if not ((select auth.role()) = 'service_role' or public.is_admin()) then
    raise exception 'Tidak diizinkan' using errcode = '42501';
  end if;

  update public.borrowings
     set status = 'returned', returned_at = now()
   where id = p_borrowing_id and status in ('active', 'overdue');
  if not found then
    raise exception 'Peminjaman tidak ditemukan atau belum aktif';
  end if;
end;
$$;

-- 4.4 Buat pesanan (checkout). p_items contoh:
--   [{"kit_id": 1, "qty": 1}, {"component_id": 5, "qty": 2}]
-- Harga diambil dari database, stok berkurang atomik. Mengembalikan id pesanan.
create or replace function public.create_order(
  p_payment_method public.payment_method,
  p_items          jsonb
)
returns bigint
language plpgsql security definer
set search_path = public
as $$
declare
  v_uid      uuid := (select auth.uid());
  v_order_id bigint;
  v_item     jsonb;
  v_qty      int;
  v_kit      bigint;
  v_comp     bigint;
  v_price    numeric(12,2);
  v_total    numeric(12,2) := 0;
begin
  if v_uid is null then
    raise exception 'Silakan masuk terlebih dahulu' using errcode = '42501';
  end if;
  if not exists (select 1 from public.profiles where id = v_uid and is_verified) then
    raise exception 'Akun belum terverifikasi' using errcode = '42501';
  end if;
  if p_items is null or jsonb_typeof(p_items) <> 'array' or jsonb_array_length(p_items) = 0 then
    raise exception 'Keranjang kosong';
  end if;

  insert into public.orders (user_id, payment_method, total)
  values (v_uid, p_payment_method, 0)
  returning id into v_order_id;

  for v_item in select * from jsonb_array_elements(p_items) loop
    v_qty  := (v_item ->> 'qty')::int;
    v_kit  := nullif(v_item ->> 'kit_id', '')::bigint;
    v_comp := nullif(v_item ->> 'component_id', '')::bigint;

    if v_qty is null or v_qty <= 0 then
      raise exception 'Jumlah (qty) harus lebih dari 0';
    end if;
    if (v_kit is null) = (v_comp is null) then
      raise exception 'Isi tepat salah satu: kit_id atau component_id';
    end if;

    if v_kit is not null then
      update public.kits set stock = stock - v_qty
       where id = v_kit and stock >= v_qty
       returning price into v_price;
    else
      update public.components set stock = stock - v_qty
       where id = v_comp and stock >= v_qty
       returning price into v_price;
    end if;

    if not found then
      raise exception 'Stok tidak mencukupi atau item tidak ditemukan';
    end if;

    insert into public.order_items (order_id, kit_id, component_id, qty, unit_price)
    values (v_order_id, v_kit, v_comp, v_qty, v_price);

    v_total := v_total + v_price * v_qty;
  end loop;

  update public.orders set total = v_total where id = v_order_id;
  return v_order_id;
end;
$$;

-- 4.5 Pengguna membatalkan pesanannya sendiri (hanya saat masih pending)
create or replace function public.cancel_my_order(p_order_id bigint)
returns void
language plpgsql security definer
set search_path = public
as $$
begin
  update public.orders set status = 'cancelled'
   where id = p_order_id
     and user_id = (select auth.uid())
     and status = 'pending';
  if not found then
    raise exception 'Pesanan tidak ditemukan atau tidak bisa dibatalkan';
  end if;
end;
$$;

-- 4.6 Statistik dasbor admin (kartu di halaman Dasbor)
create or replace function public.admin_dashboard_stats()
returns jsonb
language plpgsql stable security definer
set search_path = public
as $$
begin
  if not public.is_admin() then
    raise exception 'Hanya admin' using errcode = '42501';
  end if;

  return jsonb_build_object(
    'total_projects',      (select count(*) from public.projects where status = 'published'),
    'active_borrowings',   (select count(*) from public.borrowings where status in ('active', 'overdue')),
    'pending_projects',    (select count(*) from public.projects where status = 'pending'),
    'pending_borrowings',  (select count(*) from public.borrowings where status = 'pending'),
    'lockers_available',   (select count(*) from public.lockers where status = 'available'),
    'lockers_total',       (select count(*) from public.lockers),
    'low_stock_components',(select count(*) from public.components where stock <= low_stock_limit)
  );
end;
$$;

-- 4.7 Tugas harian: tandai terlambat + pengingat H-1 (dijadwalkan lewat pg_cron)
create or replace function public.send_return_reminders()
returns void
language plpgsql security definer
set search_path = public
as $$
begin
  update public.borrowings
     set status = 'overdue'
   where status = 'active' and due_date < current_date;

  insert into public.notifications (user_id, type, message)
  select user_id, 'return_reminder',
         'Pengingat: peminjaman #' || id || ' jatuh tempo besok (' || to_char(due_date, 'DD-MM-YYYY') || ').'
    from public.borrowings
   where status = 'active' and due_date = current_date + 1;
end;
$$;

revoke execute on function public.send_return_reminders() from public, anon, authenticated;

-- Aktifkan pg_cron di Dashboard > Database > Extensions, lalu jalankan (01:00 UTC = 08:00 WIB):
-- select cron.schedule('return-reminders', '0 1 * * *', $$ select public.send_return_reminders(); $$);

-- ---------------------------------------------------------------------
-- 5. ROW LEVEL SECURITY
-- ---------------------------------------------------------------------
alter table public.profiles                 enable row level security;
alter table public.email_verifications      enable row level security;
alter table public.projects                 enable row level security;
alter table public.saved_projects           enable row level security;
alter table public.components               enable row level security;
alter table public.project_components       enable row level security;
alter table public.kits                     enable row level security;
alter table public.kit_items                enable row level security;
alter table public.hardware_units           enable row level security;
alter table public.lockers                  enable row level security;
alter table public.borrowings               enable row level security;
alter table public.qr_tokens                enable row level security;
alter table public.orders                   enable row level security;
alter table public.order_items              enable row level security;
alter table public.notifications            enable row level security;
alter table public.notification_preferences enable row level security;
alter table public.activity_logs            enable row level security;

-- email_verifications: tanpa policy = hanya service role yang bisa akses
revoke all on public.email_verifications from anon, authenticated;

-- profiles
create policy "profiles: lihat milik sendiri atau admin" on public.profiles
  for select to authenticated
  using (id = (select auth.uid()) or (select public.is_admin()));
create policy "profiles: ubah milik sendiri" on public.profiles
  for update to authenticated
  using (id = (select auth.uid()))
  with check (id = (select auth.uid()));
create policy "profiles: admin kelola semua" on public.profiles
  for all to authenticated
  using ((select public.is_admin()))
  with check ((select public.is_admin()));

-- projects (katalog publik hanya status 'published')
create policy "projects: publik lihat yang terbit, pemilik & admin lihat semua" on public.projects
  for select to anon, authenticated
  using (status = 'published'
         or creator_id = (select auth.uid())
         or (select public.is_admin()));
create policy "projects: pengguna ajukan proyek baru" on public.projects
  for insert to authenticated
  with check (creator_id = (select auth.uid()) and status = 'pending');
create policy "projects: pemilik ubah saat pending/ditolak" on public.projects
  for update to authenticated
  using (creator_id = (select auth.uid()) and status in ('pending', 'rejected'))
  with check (creator_id = (select auth.uid()) and status = 'pending');
create policy "projects: pemilik hapus saat pending/ditolak" on public.projects
  for delete to authenticated
  using (creator_id = (select auth.uid()) and status in ('pending', 'rejected'));
create policy "projects: admin kelola semua" on public.projects
  for all to authenticated
  using ((select public.is_admin()))
  with check ((select public.is_admin()));

-- saved_projects
create policy "saved_projects: kelola milik sendiri" on public.saved_projects
  for all to authenticated
  using (user_id = (select auth.uid()))
  with check (user_id = (select auth.uid()));

-- components, kits, kit_items: katalog toko publik, admin yang menulis
create policy "components: publik lihat" on public.components
  for select to anon, authenticated using (true);
create policy "components: admin kelola" on public.components
  for all to authenticated
  using ((select public.is_admin())) with check ((select public.is_admin()));

create policy "kits: publik lihat" on public.kits
  for select to anon, authenticated using (true);
create policy "kits: admin kelola" on public.kits
  for all to authenticated
  using ((select public.is_admin())) with check ((select public.is_admin()));

create policy "kit_items: publik lihat" on public.kit_items
  for select to anon, authenticated using (true);
create policy "kit_items: admin kelola" on public.kit_items
  for all to authenticated
  using ((select public.is_admin())) with check ((select public.is_admin()));

-- project_components (BOM): terlihat jika proyeknya terlihat; pemilik/admin boleh menulis
create policy "project_components: lihat jika proyek terlihat" on public.project_components
  for select to anon, authenticated
  using (exists (select 1 from public.projects p where p.id = project_id));
create policy "project_components: pemilik proyek kelola" on public.project_components
  for all to authenticated
  using (exists (select 1 from public.projects p
                  where p.id = project_id and p.creator_id = (select auth.uid())))
  with check (exists (select 1 from public.projects p
                       where p.id = project_id and p.creator_id = (select auth.uid())));
create policy "project_components: admin kelola" on public.project_components
  for all to authenticated
  using ((select public.is_admin())) with check ((select public.is_admin()));

-- hardware_units & lockers: terlihat untuk pengguna masuk (cek ketersediaan), admin menulis
create policy "hardware_units: lihat" on public.hardware_units
  for select to anon, authenticated using (true);
create policy "hardware_units: admin kelola" on public.hardware_units
  for all to authenticated
  using ((select public.is_admin())) with check ((select public.is_admin()));

create policy "lockers: pengguna masuk lihat" on public.lockers
  for select to authenticated using (true);
create policy "lockers: admin kelola" on public.lockers
  for all to authenticated
  using ((select public.is_admin())) with check ((select public.is_admin()));

-- borrowings
create policy "borrowings: lihat milik sendiri atau admin" on public.borrowings
  for select to authenticated
  using (user_id = (select auth.uid()) or (select public.is_admin()));
create policy "borrowings: ajukan (akun terverifikasi)" on public.borrowings
  for insert to authenticated
  with check (
    user_id = (select auth.uid())
    and status = 'pending'
    and locker_id is null and approved_by is null
    and picked_up_at is null and returned_at is null
    and exists (select 1 from public.profiles pr
                 where pr.id = (select auth.uid()) and pr.is_verified)
  );
create policy "borrowings: batalkan pengajuan sendiri" on public.borrowings
  for update to authenticated
  using (user_id = (select auth.uid()) and status = 'pending')
  with check (user_id = (select auth.uid())
              and status in ('pending', 'cancelled')
              and locker_id is null and approved_by is null);
create policy "borrowings: admin kelola" on public.borrowings
  for all to authenticated
  using ((select public.is_admin())) with check ((select public.is_admin()));

-- qr_tokens: dibuat lewat approve_borrowing(); pemilik peminjaman boleh melihat
create policy "qr_tokens: lihat milik peminjaman sendiri atau admin" on public.qr_tokens
  for select to authenticated
  using (exists (select 1 from public.borrowings b
                  where b.id = borrowing_id
                    and (b.user_id = (select auth.uid()) or (select public.is_admin()))));
create policy "qr_tokens: admin kelola" on public.qr_tokens
  for all to authenticated
  using ((select public.is_admin())) with check ((select public.is_admin()));

-- orders & order_items: dibuat lewat create_order(); pengguna hanya membaca miliknya
create policy "orders: lihat milik sendiri atau admin" on public.orders
  for select to authenticated
  using (user_id = (select auth.uid()) or (select public.is_admin()));
create policy "orders: admin kelola" on public.orders
  for all to authenticated
  using ((select public.is_admin())) with check ((select public.is_admin()));

create policy "order_items: lihat milik sendiri atau admin" on public.order_items
  for select to authenticated
  using (exists (select 1 from public.orders o
                  where o.id = order_id
                    and (o.user_id = (select auth.uid()) or (select public.is_admin()))));
create policy "order_items: admin kelola" on public.order_items
  for all to authenticated
  using ((select public.is_admin())) with check ((select public.is_admin()));

-- notifications: baca & tandai dibaca; pembuatan lewat trigger/fungsi
create policy "notifications: lihat milik sendiri" on public.notifications
  for select to authenticated
  using (user_id = (select auth.uid()));
create policy "notifications: tandai dibaca" on public.notifications
  for update to authenticated
  using (user_id = (select auth.uid()))
  with check (user_id = (select auth.uid()));
create policy "notifications: hapus milik sendiri" on public.notifications
  for delete to authenticated
  using (user_id = (select auth.uid()));
create policy "notifications: admin kirim/kelola" on public.notifications
  for all to authenticated
  using ((select public.is_admin())) with check ((select public.is_admin()));

-- notification_preferences
create policy "notification_preferences: kelola milik sendiri" on public.notification_preferences
  for all to authenticated
  using (user_id = (select auth.uid()))
  with check (user_id = (select auth.uid()));

-- activity_logs: hanya admin yang membaca; penulisan lewat trigger/fungsi/service role
create policy "activity_logs: admin lihat" on public.activity_logs
  for select to authenticated
  using ((select public.is_admin()));

-- ---------------------------------------------------------------------
-- 6. DATA AWAL
-- ---------------------------------------------------------------------

-- 24 locker (A-01..A-12 dan B-01..B-12) seperti pada tampilan dasbor
insert into public.lockers (code)
select prefix || '-' || lpad(n::text, 2, '0')
  from unnest(array['A', 'B']) as prefix,
       generate_series(1, 12) as n;

-- Membuat ADMIN pertama:
-- 1) Daftar akun biasa lewat aplikasi (atau Dashboard > Authentication > Add user)
-- 2) Jalankan di SQL Editor (ganti emailnya):
-- update public.profiles set role = 'admin' where email = 'admin@contoh.ac.id';