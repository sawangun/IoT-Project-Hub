@extends('layouts.admin')
@section('title','Laporan & Analitik Lab IoT | Sawangun IoT Hub')
@push('styles')<link rel="stylesheet" href="{{ asset('css/admin-system.css') }}?v=1">@endpush
@section('content')
<div class="sys-kicker">LAPORAN & ANALITIK <span>Data Supabase</span></div>
<section class="sys-title"><div><h1>Laporan & Analitik Lab IoT</h1><p>Ringkasan proyek, peminjaman, locker, dan transaksi berdasarkan data aktual.</p></div></section>
<section class="metric-grid">
@foreach([['TOTAL PEMINJAMAN',$metrics['borrowings'],'Seluruh pengajuan'],['SUDAH DIKEMBALIKAN',$metrics['returned'],'Peminjaman selesai'],['TERLAMBAT',$metrics['overdue'],'Perlu tindak lanjut'],['TOTAL PESANAN',$metrics['orders'],'Transaksi toko'],['PROYEK TERBIT',$metrics['projects_published'],'Katalog publik'],['MENUNGGU REVIEW',$metrics['projects_pending'],'Proposal pending'],['LOCKER TERSEDIA',$metrics['lockers_available'],'Dari '.$metrics['lockers_total'].' locker']] as $m)
<article><small>{{ $m[0] }}</small><h2>{{ number_format($m[1]) }}</h2><b>{{ $m[2] }}</b></article>
@endforeach
</section>
<section class="panel" style="padding:24px"><h2>Komponen paling banyak dipesan</h2><table class="table"><thead><tr><th>Komponen</th><th>Jumlah terjual</th></tr></thead><tbody>
@forelse($popularComponents as $item)<tr><td>{{ $item->name }}</td><td>{{ $item->quantity }}</td></tr>@empty<tr><td colspan="2">Belum ada transaksi komponen.</td></tr>@endforelse
</tbody></table></section>
@endsection
