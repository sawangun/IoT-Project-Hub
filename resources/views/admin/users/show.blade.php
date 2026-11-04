@extends('layouts.admin')
@section('title','Detail Pengguna | Sawangun IoT Hub')
@push('styles')<link rel="stylesheet" href="{{ asset('css/admin-users.css') }}?v=2">@endpush
@section('content')
<div class="detail-title"><div><small><a href="{{ route('admin.users.index') }}">← Daftar Pengguna</a></small><h1>Detail Profil Pengguna</h1></div></div>
<section class="profile-hero panel"><div class="big-avatar">{{ $user['initial'] }}</div><div class="hero-name"><h2>{{ $user['name'] }} <span>{{ ucfirst($user['role']) }}</span></h2><p>NIM / Identitas: <b>{{ $user['identity'] }}</b></p><p>{{ $user['email'] }}</p></div><div class="hero-stats"><span>Status verifikasi <b>{{ $user['status'] }}</b></span><span>Total peminjaman <b>{{ $user['borrowing_count'] }}</b></span><span>Proyek terdaftar <b>{{ $user['projects']->count() }}</b></span></div></section>
<div class="detail-grid"><div><section class="panel info-card"><h2>Data Profil Supabase</h2>
@foreach([['Nama',$user['name']],['NIM',$user['identity']],['Role',$user['role']],['Email',$user['email']],['Nomor HP',$user['phone']],['Verifikasi',$user['status']]] as $x)<div class="info-row"><span>{{ $x[0] }}</span><b>{{ $x[1] }}</b></div>@endforeach
</section><section class="panel project-box"><h2>Proyek yang dibuat</h2>
@forelse($user['projects'] as $project)<div class="info-row"><span>{{ $project->title }}</span><b>{{ ucfirst($project->status) }}</b></div>@empty<p>Pengguna ini belum mengajukan proyek.</p>@endforelse
</section></div>
<div><section class="panel info-card"><h2>Riwayat Peminjaman</h2>
@forelse($user['borrowings'] as $borrowing)<div class="info-row"><span>#{{ $borrowing->id }} · {{ \Carbon\Carbon::parse($borrowing->created_at)->format('d M Y') }}</span><b>{{ ucfirst($borrowing->status) }}</b></div>@empty<p>Belum ada riwayat peminjaman.</p>@endforelse
</section></div></div>
@endsection
