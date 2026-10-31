@extends('layouts.app')
@section('title', 'Dashboard Pengguna | IoT Project Hub')
@section('content')
<div class="portal-page">
  <section class="portal-hero portal-container">
    <div>
      <span class="portal-eyebrow"><i></i> SAWANGUN IoT HUB · RUANG PENGGUNA</span>
      <h1>Temukan ide. Bangun prototipe.</h1>
      <p>Jelajahi proyek IoT, cek ketersediaan kit dan komponen, lalu pantau pengajuan peminjaman dari satu tempat.</p>
      <div class="portal-hero-actions"><a class="portal-btn" href="{{ route('projects.index') }}">Jelajahi proyek <span>↗</span></a><a class="portal-btn portal-btn-light" href="{{ route('catalog.index') }}">Buka katalog kit & komponen</a></div>
    </div>
    <div class="portal-hero-visual" aria-hidden="true"><div class="circuit-orbit orbit-one"></div><div class="circuit-orbit orbit-two"></div><div class="chip-graphic"><span>IoT</span><small>PROJECT HUB</small></div><i class="node node-a"></i><i class="node node-b"></i><i class="node node-c"></i></div>
  </section>

  <section class="portal-container portal-stat-grid" aria-label="Ringkasan data">
    <article class="portal-stat"><span class="portal-stat-icon blue">⌘</span><div><small>PROYEK DIPUBLIKASIKAN</small><strong>{{ number_format($projectCount, 0, ',', '.') }}</strong><p>Proyek yang dapat dijelajahi</p></div></article>
    <article class="portal-stat"><span class="portal-stat-icon teal">▦</span><div><small>KIT TERDAFTAR</small><strong>{{ number_format($kitCount, 0, ',', '.') }}</strong><p>Paket perangkat dalam katalog</p></div></article>
    <article class="portal-stat"><span class="portal-stat-icon violet">⌁</span><div><small>JENIS KOMPONEN</small><strong>{{ number_format($componentCount, 0, ',', '.') }}</strong><p>Komponen dan sensor</p></div></article>
    <article class="portal-stat"><span class="portal-stat-icon green">▣</span><div><small>UNIT SIAP DIPINJAM</small><strong>{{ number_format($availableUnits, 0, ',', '.') }}</strong><p>{{ number_format($availableLockers, 0, ',', '.') }} loker berstatus tersedia</p></div></article>
  </section>

  <section class="portal-container portal-section">
    <div class="portal-section-heading"><div><span class="portal-kicker">MULAI DARI SINI</span><h2>Akses cepat</h2><p>Fitur utama yang terhubung dengan data di sistem.</p></div></div>
    <div class="portal-shortcuts">
      <a href="{{ route('projects.index') }}" class="portal-shortcut"><span class="shortcut-icon blue">⌘</span><div><strong>Eksplorasi proyek</strong><p>Pelajari detail, tingkat kesulitan, dan daftar komponen.</p></div><b>↗</b></a>
      <a href="{{ route('catalog.index') }}" class="portal-shortcut"><span class="shortcut-icon teal">▦</span><div><strong>Kit & komponen</strong><p>Periksa harga dan stok kit serta komponen satuan.</p></div><b>↗</b></a>
      <a href="{{ route('borrow.index') }}" class="portal-shortcut"><span class="shortcut-icon violet">▣</span><div><strong>Peminjaman & smart locker</strong><p>Lihat unit tersedia dan riwayat pengajuan Anda.</p></div><b>↗</b></a>
    </div>
  </section>

  <section class="portal-container portal-section">
    <div class="portal-section-heading portal-heading-row"><div><span class="portal-kicker">REPOSITORI</span><h2>Proyek terbaru</h2><p>Hanya proyek dengan status <code>published</code> yang tampil untuk pengguna.</p></div><a class="portal-text-link" href="{{ route('projects.index') }}">Lihat semua proyek ↗</a></div>
    @if($publishedProjects->isEmpty())
      <div class="portal-empty"><span>⌘</span><h3>Belum ada proyek publik</h3><p>Proyek akan muncul di sini setelah admin mengubah statusnya menjadi dipublikasikan.</p></div>
    @else
      <div class="portal-project-grid">
        @foreach($publishedProjects as $project)
          <article class="portal-project-card"><div class="project-card-art art-{{ $loop->index % 3 }}"><span>IoT PROJECT</span><b>{{ strtoupper(mb_substr($project->title, 0, 1)) }}</b><small>{{ ucfirst($project->difficulty) }}</small></div><div class="portal-project-copy"><span class="portal-card-status"><i></i> Dipublikasikan</span><h3>{{ $project->title }}</h3><p>{{ \Illuminate\Support\Str::limit($project->description ?: 'Belum ada deskripsi untuk proyek ini.', 110) }}</p><a href="{{ route('projects.show', $project->id) }}">Lihat detail proyek <span>→</span></a></div></article>
        @endforeach
      </div>
    @endif
  </section>

  <section class="portal-container portal-section portal-loan-summary">
    <div><span class="portal-kicker">PEMINJAMAN SAYA</span><h2>Ringkasan peminjaman</h2><p>Data pribadi ditampilkan jika akun masuk sudah terhubung ke tabel <code>profiles</code>.</p></div>
    <div class="loan-summary-number"><strong>{{ $activeBorrowingCount }}</strong><span>Peminjaman aktif</span><small>{{ $myBorrowingCount }} total pengajuan</small></div>
    @if(!$profileReady)
      <div class="portal-notice"><span>ⓘ</span><p>Akun login saat ini belum terhubung ke profil Supabase. Katalog tetap bisa dilihat, tetapi riwayat dan pengajuan peminjaman pribadi akan tersedia setelah autentikasi terhubung.</p></div>
    @elseif($myBorrowings->isEmpty())
      <div class="portal-notice"><span>✓</span><p>Belum ada pengajuan peminjaman. Pilih unit yang tersedia dari halaman peminjaman untuk memulai.</p></div>
    @else
      <div class="portal-loan-list">@foreach($myBorrowings as $loan)<div class="portal-loan-row"><div><strong>{{ $loan->project_title ?: 'Proyek tidak tersedia' }}</strong><small>{{ $loan->serial_code ?: 'Unit tidak tersedia' }} · {{ $loan->locker_code ?: 'Loker belum ditentukan' }}</small></div><span class="status-pill status-{{ $loan->status }}">{{ ucfirst($loan->status) }}</span></div>@endforeach</div>
    @endif
    <a class="portal-btn portal-btn-light" href="{{ route('borrow.index') }}">Buka halaman peminjaman ↗</a>
  </section>
</div>
@endsection
