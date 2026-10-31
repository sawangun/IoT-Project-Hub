@extends('layouts.app')
@section('title', 'Checkout Pembelian | IoT Project Hub')
@section('content')
<div class="portal-page checkout-page">
<section class="portal-container checkout-hero"><div class="detail-breadcrumb"><a href="{{ route('dashboard') }}">Beranda</a><span>/</span><a href="{{ route('catalog.index') }}">Kit & Komponen</a><span>/</span><span>Checkout Pembelian</span></div><span class="portal-kicker">TRANSAKSI MANDIRI LAB TERPADU</span><h1>Checkout & Konfirmasi Pembelian Komponen</h1><p>Selesaikan pemesanan perangkat dan komponen IoT laboratorium dengan data pemesan dan metode pembayaran yang sesuai.</p></section>
@if(!$item)<section class="portal-container"><div class="portal-empty"><h3>Item belum dipilih</h3><p>Pilih kit atau komponen dari katalog terlebih dahulu untuk melanjutkan checkout.</p><a class="portal-btn" href="{{ route('catalog.index') }}">Kembali ke katalog</a></div></section>
@else
<form method="POST" action="{{ route('checkout.store') }}" class="portal-container checkout-grid">@csrf
<input type="hidden" name="item_type" value="{{ $type }}"><input type="hidden" name="item_id" value="{{ $id }}">
<div class="checkout-main">
 <section class="checkout-panel"><div class="checkout-step"><b>1</b><div><h2>Data Pemesan & Autentikasi Pengambil</h2><p>Pastikan informasi kontak dapat dihubungi oleh pengelola laboratorium.</p></div></div>
 <div class="checkout-fields"><label>Nama lengkap<input name="customer_name" value="{{ old('customer_name', auth()->user()->name ?? '') }}" required maxlength="120"></label><label>Email aktif<input type="email" name="customer_email" value="{{ old('customer_email', auth()->user()->email ?? '') }}" required maxlength="160"></label><label>Nomor WhatsApp<input name="customer_phone" value="{{ old('customer_phone') }}" required maxlength="30" placeholder="+62..."></label></div>
 @error('customer_name')<small class="field-error">{{ $message }}</small>@enderror @error('customer_email')<small class="field-error">{{ $message }}</small>@enderror @error('customer_phone')<small class="field-error">{{ $message }}</small>@enderror
 </section>
 <section class="checkout-panel"><div class="checkout-step"><b>2</b><div><h2>Alokasi Komponen & Kuantitas</h2><p>Periksa item dan jumlah sebelum membuat pesanan.</p></div></div>
 <article class="checkout-item"><div class="checkout-item-icon">⌁</div><div><strong>{{ $item->name }}</strong><small>{{ $type === 'kit' ? 'Paket kit proyek' : 'Komponen satuan' }} · Stok tersedia {{ $item->stock }}</small></div><label>Qty<input type="number" name="quantity" min="1" max="{{ min(20,(int)$item->stock) }}" value="{{ old('quantity',$quantity) }}" required></label><strong>Rp{{ number_format((float)$item->price,0,',','.') }}</strong></article>
 @error('quantity')<small class="field-error">{{ $message }}</small>@enderror
 </section>
 <section class="checkout-panel"><div class="checkout-step"><b>3</b><div><h2>Metode Pembayaran (Simulasi Demo)</h2><p>Pilih metode untuk simulasi. Tidak ada transaksi uang sungguhan.</p></div></div>
 <div class="payment-options"><label><input type="radio" name="payment_method" value="qris" checked><span><b>QRIS (Simulasi)</b><small>Tampilan pembayaran QRIS contoh, bukan QR aktif</small></span></label><label><input type="radio" name="payment_method" value="edupay"><span><b>EduPay (Simulasi)</b><small>Simulasi pembayaran saldo internal</small></span></label><label><input type="radio" name="payment_method" value="virtual_account"><span><b>Virtual Account (Simulasi)</b><small>Nomor VA contoh, tidak dapat digunakan untuk transfer</small></span></label></div>
 </section>
 <section class="checkout-panel"><label class="notes-label">Catatan / Instruksi Khusus (Opsional)<textarea name="notes" maxlength="500" rows="3" placeholder="Tambahkan catatan untuk pengelola lab...">{{ old('notes') }}</textarea></label></section>
 <label class="checkout-consent"><input type="checkbox" required> Saya memastikan data pemesan benar dan memahami bahwa pesanan menunggu konfirmasi pengelola.</label>
</div>
<aside class="checkout-summary"><span class="portal-kicker">RINGKASAN BIAYA</span><h2>Pesanan kamu</h2><div class="summary-line"><span>{{ $item->name }}</span><strong>Rp{{ number_format((float)$item->price,0,',','.') }}</strong></div><div class="summary-line"><span>Stok tersedia</span><strong>{{ $item->stock }}</strong></div><div class="summary-total"><small>Subtotal per item</small><strong>Rp{{ number_format((float)$item->price,0,',','.') }}</strong></div><button class="portal-btn checkout-submit" type="submit">Buat Pesanan <span>→</span></button><p class="checkout-note">MODE DEMO: pembayaran hanya simulasi. Tidak ada uang yang ditarik atau diterima.</p><div class="checkout-security">✓ Data transaksi dikirim melalui koneksi aplikasi.</div><a class="checkout-back" href="{{ route('catalog.index') }}">← Kembali ke katalog</a></aside>
</form>
@endif
</div>
@endsection
