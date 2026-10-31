@extends('layouts.app')
@section('title', 'Riwayat Transaksi & Peminjaman | IoT Project Hub')
@section('content')
<div class="portal-page history-page">
<section class="portal-container history-hero">
 <div><div class="detail-breadcrumb"><a href="{{ route('dashboard') }}">Beranda</a><span>/</span><span>Riwayat Transaksi & Peminjaman</span></div>
 <span class="portal-kicker">NEKUS TELEMETRY & LOCKER AUDIT</span>
 <h1>Riwayat Transaksi &<br>Peminjaman Hardware</h1>
 <p>Pantau semua riwayat pembelian komponen, peminjaman prototipe riset, serta status smart locker digital dalam satu halaman.</p>
 <div class="history-identity">◈ Identitas Pengguna: {{ auth()->user()->name ?? 'Pengunjung' }} <span>⌁ Sinkronisasi data berdasarkan akun</span></div></div>
 <div class="history-metrics">
  <article><small>Transaksi Pembelian</small><strong>{{ $purchases->count() }}</strong><span>Pesanan tercatat</span></article>
  <article><small>Peminjaman Hardware</small><strong>{{ $borrowings->count() }}</strong><span>Riwayat peminjaman</span></article>
  <article><small>Total Aktivitas</small><strong>{{ $purchases->count() + $borrowings->count() }}</strong><span>Pembelian & peminjaman</span></article>
  <article><small>Status Akun</small><strong>{{ $profileId ? 'Aktif' : '—' }}</strong><span>{{ $profileId ? 'Profil terhubung' : 'Profil kampus belum terhubung' }}</span></article>
 </div>
</section>
@if(session('success'))<div class="portal-container"><div class="portal-alert success">{{ session('success') }}</div></div>@endif
<section class="portal-container history-content">
 <div class="history-tabs"><a class="active" href="#all">Semua Aktivitas</a><a href="#purchases">Pembelian Komponen ({{ $purchases->count() }})</a><a href="#loans">Peminjaman Prototipe ({{ $borrowings->count() }})</a></div>
 <div class="history-filter"><span>⌕</span><span>Riwayat terbaru ditampilkan lebih dahulu</span><a href="{{ route('catalog.index') }}">Belanja katalog ↗</a></div>
 <div id="all" class="portal-section-heading"><span class="portal-kicker">AKTIVITAS AKUN</span><h2>Transaksi dan peminjaman</h2><p>Data ditampilkan sesuai akun pengguna yang sedang aktif.</p></div>
 <div id="purchases" class="history-list">
 @forelse($purchases as $purchase)
 <article class="history-card">
  <div class="history-thumb">▦</div><div class="history-main"><span class="portal-kicker">#INV-{{ str_pad($purchase->id,6,'0',STR_PAD_LEFT) }} · {{ \Illuminate\Support\Carbon::parse($purchase->created_at)->format('d M Y, H:i') }}</span><h3>{{ $purchase->item_name }}</h3><p>{{ ucfirst($purchase->item_type) }} · {{ $purchase->quantity }} item · {{ strtoupper(str_replace('_',' ',$purchase->payment_method)) }}</p><span class="history-tag">Pembelian Komponen</span></div>
  <div class="history-total"><small>Total transaksi</small><strong>Rp{{ number_format((float)$purchase->total,0,',','.') }}</strong><span class="status-pill status-{{ $purchase->status }}">{{ $purchase->status === 'paid' ? 'Lunas (Simulasi)' : ucfirst(str_replace('_',' ',$purchase->status)) }}</span>@if($purchase->status === 'pending')<a href="{{ route('checkout.simulation', $purchase->id) }}">Lanjutkan simulasi pembayaran ↗</a>@endif</div>
 </article>
 @empty <div class="portal-empty"><span>▦</span><h3>Belum ada transaksi pembelian</h3><p>Pesanan dari katalog akan muncul di bagian ini.</p><a class="portal-btn" href="{{ route('catalog.index') }}">Jelajahi katalog</a></div>@endforelse
 </div>
 <div id="loans" class="history-list">
 @forelse($borrowings as $loan)
 <article class="history-card">
  <div class="history-thumb loan-thumb">⌁</div><div class="history-main"><span class="portal-kicker">#LCK-{{ str_pad($loan->id,6,'0',STR_PAD_LEFT) }} · {{ \Illuminate\Support\Carbon::parse($loan->created_at)->format('d M Y, H:i') }}</span><h3>{{ $loan->project_title ?: 'Proyek tidak tersedia' }}</h3><p>{{ $loan->serial_code ?: 'Kode unit belum tersedia' }} · Loker {{ $loan->locker_code ?: 'belum ditentukan' }}</p><span class="history-tag loan-tag">Peminjaman Prototipe</span><small>Jatuh tempo: {{ $loan->due_date ? \Illuminate\Support\Carbon::parse($loan->due_date)->format('d M Y') : 'Belum ditentukan' }}</small></div>
  <div class="history-total"><small>Status peminjaman</small><strong class="loan-status">{{ ucfirst(str_replace('_',' ',$loan->status)) }}</strong><a href="{{ route('borrow.index') }}">Detail peminjaman ↗</a></div>
 </article>
 @empty <div class="portal-empty"><span>⌁</span><h3>Belum ada riwayat peminjaman</h3><p>Pengajuan perangkat yang kamu buat akan muncul di sini.</p><a class="portal-btn" href="{{ route('borrow.index') }}">Ajukan peminjaman</a></div>@endforelse
 </div>
 <div class="history-help"><div><strong>Butuh bantuan transaksi atau pengembalian?</strong><p>Hubungi pengelola laboratorium untuk bantuan pembayaran, pengambilan perangkat, atau pengembalian hardware.</p></div><a class="portal-btn" href="{{ route('borrow.index') }}">Pusat bantuan ↗</a></div>
</section></div>
@endsection
