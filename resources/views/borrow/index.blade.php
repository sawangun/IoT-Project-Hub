@extends('layouts.app')
@section('title', ($showAvailableUnits ? 'Cari Perangkat' : 'Peminjaman Berlangsung').' | IoT Project Hub')
@section('content')
<div class="portal-page borrowing-page">
  <section class="portal-container borrowing-hero">
    <div>
      <div class="detail-breadcrumb"><a href="{{ route('dashboard') }}">Beranda</a><span>/</span><span>{{ $showAvailableUnits ? 'Cari Perangkat' : 'Peminjaman' }}</span></div>
      <span class="portal-kicker">AKTIVITAS AKUN</span>
      <h1>{{ $showAvailableUnits ? 'Cari Perangkat untuk Dipinjam' : 'Peminjaman Saya' }}</h1>
      <p>{{ $showAvailableUnits ? 'Pilih perangkat yang tersedia dan ajukan peminjaman melalui formulir di setiap unit.' : 'Pantau proyek dan perangkat yang sedang kamu pinjam, status persetujuan, serta batas waktu pengembaliannya.' }}</p>
    </div>
    @if(!$showAvailableUnits)<a class="portal-btn" href="{{ route('borrow.index', ['tab' => 'tersedia']) }}#available-units">＋ Cari Perangkat</a>
    @else<a class="portal-btn portal-btn-light" href="{{ route('borrow.index') }}">Lihat Peminjaman Saya</a>@endif
  </section>
  @if(session('success'))<div class="portal-container"><div class="portal-alert success">✓ {{ session('success') }}</div></div>@endif
  @if($showAvailableUnits)
    <section class="portal-container portal-section" id="available-units">
      <div class="portal-section-heading"><span class="portal-kicker">UNIT TERSEDIA</span><h2>{{ $selectedProjectId ? 'Unit tersedia untuk proyek ini' : 'Perangkat yang bisa dipinjam' }}</h2><p>Ajukan perangkat dengan memilih tanggal pengembalian.</p></div>
      @if($availableUnits->isEmpty())<div class="portal-empty"><span>⌁</span><h3>{{ $selectedProjectId ? 'Belum ada unit tersedia untuk proyek ini' : 'Belum ada perangkat tersedia' }}</h3><p>{{ $selectedProjectId ? 'Proyek ini belum memiliki unit fisik berstatus tersedia, atau belum dipublikasikan.' : 'Coba periksa kembali nanti.' }}</p></div>
      @else<div class="available-unit-list">@foreach($availableUnits as $unit)<article class="available-unit-card"><div class="available-unit-icon">⌁</div><div class="available-unit-copy"><h3>{{ $unit->title }}</h3><p>Kode unit: <strong>{{ $unit->serial_code }}</strong> · Kesulitan {{ ucfirst($unit->difficulty) }}</p><span class="unit-condition">Kondisi: {{ ucfirst(str_replace('_',' ',$unit->condition)) }}</span></div>@if($profileId)<form method="POST" action="{{ route('borrow.store') }}" class="borrow-request-form">@csrf<input type="hidden" name="unit_id" value="{{ $unit->id }}"><label for="due-{{ $unit->id }}">Tanggal pengembalian</label><input id="due-{{ $unit->id }}" type="date" name="due_date" min="{{ now()->toDateString() }}" max="{{ now()->addDays(30)->toDateString() }}" value="{{ now()->addDays(7)->toDateString() }}" required><button type="submit" class="portal-btn">Ajukan</button></form>@else<a class="portal-btn portal-btn-light" href="{{ route('login') }}">Masuk untuk pinjam</a>@endif</article>@endforeach</div>@endif
    </section>
  @else
    <section class="portal-container borrow-stats borrow-stats-focused">
      <article><span class="summary-icon blue">▣</span><div><strong>{{ $borrowings->count() }}</strong><small>Peminjaman berjalan</small></div></article>
      <article><span class="summary-icon teal">◷</span><div><strong>{{ $borrowings->where('status', 'pending')->count() }}</strong><small>Menunggu persetujuan</small></div></article>
      <article><span class="summary-icon violet">✓</span><div><strong>{{ $borrowings->whereIn('status', ['approved', 'active'])->count() }}</strong><small>Disetujui / dipinjam</small></div></article>
      <article><span class="summary-icon green">!</span><div><strong>{{ $borrowings->where('status', 'overdue')->count() }}</strong><small>Terlambat</small></div></article>
    </section>
    <section class="portal-container portal-section borrow-current-section">
      <div class="portal-section-heading portal-heading-row"><div><span class="portal-kicker">DAFTAR AKTIVITAS</span><h2>Proyek yang sedang kamu pinjam</h2><p>Pengajuan yang masih diproses dan pinjaman yang belum selesai.</p></div><a class="portal-text-link" href="{{ route('user.history') }}">Lihat riwayat →</a></div>
      @if(!$profileId)<div class="portal-notice borrow-login-notice"><span>ⓘ</span><p>Akun belum terhubung dengan profil kampus. Hubungi pengelola agar data peminjaman dapat ditampilkan.</p></div>
      @elseif($borrowings->isEmpty())<div class="portal-empty"><span>▣</span><h3>Belum ada peminjaman yang sedang berlangsung</h3><p>Perangkat yang kamu ajukan atau pinjam akan muncul di halaman ini.</p><a class="portal-btn" href="{{ route('borrow.index', ['tab' => 'tersedia']) }}#available-units">Cari Perangkat</a></div>
      @else<div class="borrow-current-list">@foreach($borrowings as $loan)<article class="borrow-current-card"><div class="borrow-current-icon">⌁</div><div class="borrow-current-info"><span class="portal-kicker">PEMINJAMAN #{{ $loan->id }}</span><h3>{{ $loan->project_title ?: 'Proyek tidak tersedia' }}</h3><p>{{ $loan->serial_code ?: 'Kode unit tidak tersedia' }}@if($loan->locker_code) · Loker {{ $loan->locker_code }}@endif</p><div class="borrow-current-meta"><span>Mulai {{ \Illuminate\Support\Carbon::parse($loan->start_date ?: $loan->created_at)->format('d M Y') }}</span><span>Batas kembali {{ \Illuminate\Support\Carbon::parse($loan->due_date)->format('d M Y') }}</span></div></div><div style="display:flex;flex-direction:column;gap:8px;align-items:flex-end"><span class="status-pill status-{{ $loan->status }}">{{ ucfirst(str_replace('_',' ',$loan->status)) }}</span>@if($loan->status==='approved')<a class="portal-btn" href="{{ route('borrow.ticket',$loan->id) }}">Lihat Tiket QR</a>@endif</div></article>@endforeach</div>@endif
    </section>
    <section class="portal-container portal-section" id="panduan"><div class="borrow-guide-panel"><div><span class="portal-kicker">BUTUH BANTUAN?</span><h2>Panduan Peminjaman</h2><p>Alur singkat peminjaman perangkat melalui IoT Project Hub.</p></div><div class="borrow-guide-steps"><article><b>01</b><div><strong>Pilih perangkat</strong><p>Buka Cari Perangkat dan pilih unit yang tersedia.</p></div></article><article><b>02</b><div><strong>Tunggu persetujuan</strong><p>Pantau status pengajuan pada halaman Peminjaman Saya.</p></div></article><article><b>03</b><div><strong>Ambil dan kembalikan</strong><p>Ikuti instruksi pengelola dan kembalikan sebelum tenggat.</p></div></article></div><a class="portal-text-link" href="{{ route('borrow.index', ['tab' => 'tersedia']) }}#available-units">Lihat perangkat tersedia →</a></div></section>
  @endif
</div>
@endsection
