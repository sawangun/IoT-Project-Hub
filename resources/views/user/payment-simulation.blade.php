@extends('layouts.app')
@section('title', 'Simulasi Pembayaran | IoT Project Hub')
@section('content')
<div class="portal-page checkout-page">
 <section class="portal-container checkout-hero">
  <div class="detail-breadcrumb"><a href="{{ route('dashboard') }}">Beranda</a><span>/</span><a href="{{ route('user.history') }}">Riwayat Transaksi</a><span>/</span><span>Simulasi Pembayaran</span></div>
  <span class="portal-kicker">DEMO TRANSAKSI · TANPA UANG SUNGGUHAN</span>
  <h1>Simulasi Pembayaran</h1>
  <p>Halaman ini hanya untuk menguji alur pembelian. Tidak ada pembayaran sungguhan yang diproses.</p>
 </section>
 <section class="portal-container" style="max-width:760px">
  @if(session('success'))<div class="portal-alert success">{{ session('success') }}</div>@endif
  <div class="checkout-panel">
   <span class="portal-kicker">PESANAN #{{ str_pad($purchase->id, 6, '0', STR_PAD_LEFT) }}</span>
   <h2>{{ $purchase->item_name }}</h2>
   <p>{{ ucfirst($purchase->item_type) }} · {{ $purchase->quantity }} item</p>
   <div class="summary-line"><span>Metode simulasi</span><strong>{{ strtoupper(str_replace('_', ' ', $purchase->payment_method)) }}</strong></div>
   <div class="summary-total"><small>Total simulasi</small><strong>Rp{{ number_format((float) $purchase->total, 0, ',', '.') }}</strong></div>
   @if($purchase->status === 'paid')
    <div class="portal-alert success">Pembayaran simulasi sudah berhasil. Status pesanan: <strong>LUNAS</strong>.</div>
    <a class="portal-btn" href="{{ route('user.history') }}">Lihat riwayat transaksi</a>
   @elseif($purchase->status === 'pending')
    <div style="margin:22px 0;padding:22px;border:1px dashed #8b9bb4;border-radius:14px;text-align:center">
     <div style="font-size:42px;margin-bottom:8px">▦</div>
     <strong>Area Pembayaran {{ strtoupper(str_replace('_', ' ', $purchase->payment_method)) }}</strong>
     <p style="margin:8px 0 0">Ini hanya ilustrasi metode pembayaran. Tidak ada QR aktif atau nomor rekening/VA sungguhan.</p>
    </div>
    <form method="POST" action="{{ route('checkout.simulation.success', $purchase->id) }}" onsubmit="return confirm('Jalankan simulasi pembayaran berhasil? Tidak ada uang sungguhan yang diproses.');">
     @csrf
     <button class="portal-btn checkout-submit" type="submit">Simulasikan Pembayaran Berhasil ✓</button>
    </form>
    <p class="checkout-note" style="margin-top:14px">Dengan menekan tombol di atas, status pesanan demo berubah menjadi Lunas dan stok akan dikurangi.</p>
   @else
    <div class="portal-alert">Pesanan ini berstatus {{ ucfirst(str_replace('_', ' ', $purchase->status)) }} dan tidak dapat dibayar melalui simulasi.</div>
    <a class="portal-btn" href="{{ route('user.history') }}">Kembali ke riwayat</a>
   @endif
  </div>
 </section>
</div>
@endsection
