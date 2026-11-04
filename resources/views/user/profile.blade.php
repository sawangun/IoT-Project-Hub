@extends('layouts.app')
@section('title', 'Profil Saya | IoT Project Hub')
@push('styles')<link rel="stylesheet" href="{{ asset('css/user-profile.css') }}?v=1">@endpush
@section('content')
<div class="portal-page user-profile-page">
  <div class="profile-wrap">
    <div class="profile-breadcrumb"><a href="{{ route('dashboard') }}">Beranda</a><span>/</span><b>Profil Saya</b></div>
    @if(session('success'))<div class="profile-alert">{{ session('success') }}</div>@endif
    <section class="profile-cover">
      <div class="profile-avatar">{{ strtoupper(mb_substr($user->name ?? 'P', 0, 1)) }}</div>
      <div class="profile-identity"><h1>{{ $user->name }}</h1><span class="profile-role">{{ ucfirst($user->role ?? 'pengguna') }}</span><p>{{ $user->email }}</p><div class="profile-tags"><span>● Akun aktif</span><span>♧ {{ $user->is_verified ? 'Terverifikasi' : 'Belum terverifikasi' }}</span></div></div>
      <a class="profile-edit-link" href="#edit-profile">✎ Edit Profil</a>
    </section>
    <section class="profile-stats">
      <article><strong>{{ $savedCount }}</strong><span>Proyek disukai</span></article>
      <article><strong>{{ $borrowingCount }}</strong><span>Total peminjaman</span></article>
      <article><strong>{{ $projectCount }}</strong><span>Proyek dibuat</span></article>
    </section>
    <div class="profile-columns">
      <aside class="profile-side">
        <a class="active" href="#academic">▣ Informasi Akun</a>
        <a href="#edit-profile">✎ Edit Profil</a>
        <a href="{{ route('projects.saved') }}">♡ Proyek Disukai <small>{{ $savedCount }}</small></a>
        <a href="{{ route('user.history') }}">◷ Riwayat Transaksi</a>
        <a href="{{ route('borrow.index') }}">▤ Peminjaman Saya</a>
        <div class="profile-side-note"><b>IoT Project Hub</b><p>Kelola informasi akun dan pantau aktivitas proyek serta peminjamanmu.</p></div>
      </aside>
      <main class="profile-main">
        <section class="profile-panel" id="academic"><div class="profile-panel-title"><span>▣</span><div><h2>Informasi Akun</h2><p>Informasi dasar yang terhubung dengan akun pengguna.</p></div></div>
          <div class="profile-info-grid"><div><small>NAMA LENGKAP</small><b>{{ $user->name }}</b></div><div><small>EMAIL</small><b>{{ $user->email }}</b></div><div><small>NIM / IDENTITAS</small><b>{{ $user->nim ?: 'Belum diisi' }}</b></div><div><small>NOMOR TELEPON</small><b>{{ $user->phone ?: 'Belum diisi' }}</b></div><div><small>ROLE AKUN</small><b>{{ ucfirst($user->role ?? 'pengguna') }}</b></div><div><small>STATUS VERIFIKASI</small><b>{{ $user->is_verified ? 'Terverifikasi' : 'Belum terverifikasi' }}</b></div></div>
        </section>
        <section class="profile-panel" id="edit-profile"><div class="profile-panel-title"><span>✎</span><div><h2>Edit Profil</h2><p>Perbarui nama, identitas, dan nomor kontak.</p></div></div>
          <form method="POST" action="{{ route('user.profile.update') }}" class="profile-edit-form">@csrf @method('PUT')
            <label>Nama lengkap<input name="name" value="{{ old('name', $user->name) }}" required maxlength="255">@error('name')<small class="field-error">{{ $message }}</small>@enderror</label>
            <label>Email<input value="{{ $user->email }}" disabled><small>Email dikelola melalui autentikasi akun.</small></label>
            <label>NIM / Identitas<input name="nim" value="{{ old('nim', $user->nim) }}" maxlength="50" placeholder="Masukkan NIM jika ada">@error('nim')<small class="field-error">{{ $message }}</small>@enderror</label>
            <label>Nomor telepon<input name="phone" value="{{ old('phone', $user->phone) }}" maxlength="30" placeholder="Contoh: 08xxxxxxxxxx">@error('phone')<small class="field-error">{{ $message }}</small>@enderror</label>
            <div class="profile-form-actions"><button type="reset" class="profile-cancel">Reset</button><button type="submit" class="profile-save">Simpan Perubahan</button></div>
          </form>
        </section>
        <section class="profile-panel"><div class="profile-panel-title"><span>♡</span><div><h2>Proyek Disukai</h2><p>Proyek yang kamu tandai untuk dilihat kembali.</p></div><a href="{{ route('projects.saved') }}">Lihat semua ↗</a></div>
          @forelse($savedProjects as $project)<a class="profile-saved-row" href="{{ route('projects.show', $project->id) }}"><span>♡</span><div><b>{{ $project->title }}</b><small>{{ ucfirst($project->difficulty) }} · {{ \Illuminate\Support\Str::limit($project->description, 80) }}</small></div><strong>→</strong></a>@empty<div class="profile-empty">Belum ada proyek disukai. Jelajahi proyek dan tekan ♡ untuk menyimpannya.</div>@endforelse
        </section>
      </main>
    </div>
  </div>
</div>
@endsection
