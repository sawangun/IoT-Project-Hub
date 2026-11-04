@extends('layouts.admin')
@section('title','Persetujuan Proyek IoT | Sawangun IoT Hub')
@push('styles')<link rel="stylesheet" href="{{ asset('css/admin-approvals.css') }}?v=3">@endpush
@section('content')
<div class="ap-page">
<section class="ap-title"><div><small>MANAJEMEN & VERIFIKASI</small><h1>Persetujuan Proyek IoT</h1><p>Daftar proposal yang menunggu keputusan admin, diambil langsung dari Supabase.</p></div></section>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<div class="panel" style="padding:24px">
<h2>Proposal menunggu review</h2>
<div class="table-responsive"><table class="table align-middle"><thead><tr><th>Proyek</th><th>Pengaju</th><th>Level</th><th>Dibuat</th><th>Status</th><th>Tindakan</th></tr></thead><tbody>
@forelse($projects as $project)
<tr><td><strong>{{ $project->title }}</strong><br><small>{{ $project->description }}</small></td><td>{{ $project->creator_name ?? 'Pengguna dihapus' }}<br><small>{{ $project->creator_email }}</small></td><td>{{ ucfirst($project->difficulty) }}</td><td>{{ \Carbon\Carbon::parse($project->created_at)->format('d M Y') }}</td><td>{{ ucfirst($project->status) }}</td><td>
@if($project->status==='pending')
<form method="POST" action="{{ route('admin.approvals.projects.approve',$project->id) }}" style="display:inline">@csrf<button class="approve" type="submit">Setujui</button></form>
<form method="POST" action="{{ route('admin.approvals.projects.reject',$project->id) }}" style="display:inline">@csrf<button class="reject" type="submit">Tolak</button></form>
@else <span>Perlu diajukan ulang</span> @endif
</td></tr>
@empty<tr><td colspan="6">Tidak ada proposal yang menunggu review.</td></tr>@endforelse
</tbody></table></div></div></div>
@endsection
