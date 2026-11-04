@extends('layouts.admin')
@section('title', 'Formulir Peminjaman Baru | Sawangun IoT Hub')
@push('styles')<link rel="stylesheet" href="{{ asset('css/admin-borrowings.css') }}?v=2">@endpush
@section('content')
<div class="br-page br-form-page">
<a class="br-back" href="{{ route('admin.borrowings.index') }}">← Kembali ke daftar peminjaman</a>
<section class="br-heading br-form-heading"><div><p class="br-eyebrow">ADMIN / REGISTRASI PEMINJAMAN</p><h1>Formulir Peminjaman Hardware</h1><p>Pilih akun dan unit hardware yang terdaftar di Supabase.</p></div><span class="br-form-chip">Supabase Database</span></section>
@if($errors->any())<div class="br-alert br-error"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ route('admin.borrowings.store') }}" class="br-create-layout">@csrf
<div class="br-form-main">
<section class="br-form-card"><div class="br-form-section"><span>01</span><div><h2>Identitas peminjam</h2><p>Pilih akun Supabase Auth.</p></div></div>
<label class="br-field"><span>Pengguna <b>*</b></span><select name="user_id" required><option value="">Pilih pengguna</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected(old('user_id')===$user->id)>{{ $user->name }} — {{ $user->nim ?: $user->email }}</option>@endforeach</select></label></section>
<section class="br-form-card"><div class="br-form-section"><span>02</span><div><h2>Unit hardware & durasi</h2><p>Hanya unit tersedia yang dapat dipilih.</p></div></div>
<label class="br-field"><span>Unit / proyek <b>*</b></span><select name="unit_id" required><option value="">Pilih unit hardware</option>@foreach($units as $unit)<option value="{{ $unit->id }}" @selected((string)old('unit_id')===(string)$unit->id)>{{ $unit->title }} — {{ $unit->serial_code }}</option>@endforeach</select></label>
<div class="br-field-row"><label class="br-field"><span>Tanggal mulai <b>*</b></span><input type="date" name="start" value="{{ old('start',now()->toDateString()) }}" required></label><label class="br-field"><span>Durasi <b>*</b></span><select name="duration" required>@foreach([3,7,14,30] as $days)<option value="{{ $days }}" @selected((string)old('duration','7')===(string)$days)>{{ $days }} hari</option>@endforeach</select></label></div>
<label class="br-field"><span>Catatan tambahan</span><textarea name="notes" rows="3">{{ old('notes') }}</textarea></label></section>
<section class="br-form-card"><div class="br-form-section"><span>03</span><div><h2>Alokasi Smart Locker</h2><p>Opsional, pilih locker tersedia.</p></div></div>
<label class="br-field"><span>Locker</span><select name="locker_id"><option value="">Belum dialokasikan</option>@foreach($lockers as $locker)<option value="{{ $locker->id }}" @selected((string)old('locker_id')===(string)$locker->id)>{{ $locker->code }}</option>@endforeach</select></label></section>
</div>
<aside class="br-form-aside"><section class="br-preview-card"><div class="br-preview-head"><span>▣</span><div><h2>Informasi</h2><p>Data disimpan permanen di Supabase.</p></div></div><div class="br-summary-note"><span>ⓘ</span><p>Peminjaman baru selalu dibuat dengan status <b>pending</b>. Persetujuan dan penerbitan QR dilakukan setelah data tersimpan.</p></div></section></aside>
<div class="br-form-actions"><a class="br-secondary" href="{{ route('admin.borrowings.index') }}">Batalkan</a><button type="submit" class="br-primary">＋ Buat peminjaman</button></div>
</form></div>
@endsection
