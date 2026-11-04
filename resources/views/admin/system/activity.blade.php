@extends('layouts.admin')
@section('title','Log Aktivitas & Audit Trail | Sawangun IoT Hub')
@push('styles')<link rel="stylesheet" href="{{ asset('css/admin-system.css') }}?v=1"><link rel="stylesheet" href="{{ asset('css/admin-workflow.css') }}?v=1">@endpush
@section('content')
<div class="activity-log-page">
<div class="sys-kicker">SISTEM & AUDIT <span>Supabase Database</span></div>
<section class="sys-title"><div><small>AUDIT TRAIL</small><h1>Log Aktivitas & Audit Trail</h1><p>Aktivitas yang tercatat pada tabel activity_logs di Supabase.</p></div></section>
<section class="metric-grid"><article><small>TOTAL LOG</small><h2>{{ number_format($total) }}</h2><b>Catatan database</b></article><article><small>DITAMPILKAN</small><h2>{{ $logs->count() }}</h2><b>100 log terbaru maksimal</b></article></section>
<section class="panel" style="padding:24px"><h2>Aktivitas terbaru</h2><div class="table-responsive"><table class="table"><thead><tr><th>Waktu</th><th>Pengguna</th><th>Locker</th><th>Aktivitas</th></tr></thead><tbody>
@forelse($logs as $log)<tr><td>{{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i:s') }}</td><td>{{ $log->user_name ?? 'Sistem' }}</td><td>{{ $log->locker_code ?? '—' }}</td><td>{{ $log->action }}</td></tr>
@empty<tr><td colspan="4">Belum ada log aktivitas di Supabase.</td></tr>@endforelse
</tbody></table></div></section>
</div>
@endsection
