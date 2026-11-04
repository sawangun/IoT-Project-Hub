@extends('layouts.admin')
@section('title','Tambah Pengguna Baru | Sawangun IoT Hub')
@push('styles')<link rel="stylesheet" href="{{ asset('css/admin-users.css') }}?v=2">@endpush
@section('content')
<div class="create-title"><div><small>Pengguna › <b>Registrasi Pengguna Baru</b></small><h1>Buat Akun Pengguna</h1><p>Akun dibuat melalui Supabase Auth. Profil public.profiles dibuat otomatis oleh trigger on_auth_user_created.</p></div><div><a class="btn ghost" href="{{ route('admin.users.index') }}">Kembali</a></div></div>
@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<form method="POST" action="{{ route('admin.users.store') }}" class="panel" style="padding:24px;max-width:760px">@csrf
<div class="fields">
<label>Nama lengkap *<input name="name" value="{{ old('name') }}" required maxlength="120"></label>
<label>NIM / NIP<input name="identity" value="{{ old('identity') }}" maxlength="50"></label>
<label>Email *<input type="email" name="email" value="{{ old('email') }}" required maxlength="160"></label>
<label>Nomor HP<input name="phone" value="{{ old('phone') }}" maxlength="30"></label>
<label>Role *<select name="role" required><option value="mahasiswa" @selected(old('role','mahasiswa')==='mahasiswa')>Mahasiswa</option><option value="umum" @selected(old('role')==='umum')>Umum</option></select></label>
<label>Password awal *<input type="password" name="password" minlength="8" required autocomplete="new-password"></label>
</div>
<p class="text-muted">Password dikirim langsung ke Supabase Auth dari server dan tidak disimpan di tabel profiles. Pastikan SUPABASE_SERVICE_ROLE_KEY hanya ada di .env server.</p>
<button class="btn primary" type="submit">Buat akun di Supabase Auth</button>
</form>
@endsection
