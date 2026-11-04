@extends('layouts.admin')
@section('title', 'Persetujuan Peminjaman | IoT Project Hub')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-workflow.css') }}?v=1">
@endpush
@section('content')
<div class="approval-borrowings-page">
    <div class="workflow-breadcrumb"><span>Admin Panel</span><b>/</b><strong>Persetujuan Peminjaman</strong></div>
    <section class="workflow-heading">
        <div>
            <span class="workflow-eyebrow">MANAJEMEN &amp; VERIFIKASI</span>
            <h1>Persetujuan Peminjaman Hardware</h1>
            <p>Periksa permohonan pengguna, lalu setujui untuk menerbitkan tiket QR atau tolak permohonan.</p>
        </div>
        <span class="workflow-live"><i></i> DATA PEMINJAMAN</span>
    </section>

    @if(session('success'))
        <div class="workflow-alert success">✓ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="workflow-alert error">{{ session('error') }}</div>
    @endif

    <section class="workflow-stats" aria-label="Ringkasan persetujuan">
        <article class="pending-stat"><span class="workflow-stat-icon">◷</span><small>MENUNGGU PERSETUJUAN</small><strong>{{ $requests->where('status', 'pending')->count() }}</strong><p>Permohonan perlu diperiksa</p></article>
        <article class="approved-stat"><span class="workflow-stat-icon">✓</span><small>DISETUJUI</small><strong>{{ $requests->where('status', 'approved')->count() }}</strong><p>Tiket QR dapat diterbitkan</p></article>
        <article class="rejected-stat"><span class="workflow-stat-icon">×</span><small>DITOLAK</small><strong>{{ $requests->where('status', 'rejected')->count() }}</strong><p>Permohonan tidak disetujui</p></article>
    </section>

    <section class="workflow-table-card">
        <div class="workflow-card-heading">
            <div><h2>Daftar Pengajuan Peminjaman</h2><p>Periksa identitas peminjam, proyek, dan tanggal pengembalian sebelum mengambil tindakan.</p></div>
            <span class="workflow-count">{{ $requests->count() }} pengajuan</span>
        </div>
        @if($requests->isEmpty())
            <div class="workflow-empty"><span>▤</span><h3>Belum ada pengajuan</h3><p>Pengajuan peminjaman baru akan muncul di bagian ini.</p></div>
        @else
            <div class="workflow-table-wrap">
                <table class="workflow-table">
                    <thead><tr><th>ID / TANGGAL</th><th>PEMINJAM</th><th>PROYEK &amp; UNIT</th><th>BATAS KEMBALI</th><th>STATUS</th><th>TINDAKAN</th></tr></thead>
                    <tbody>
                    @foreach($requests as $request)
                        <tr>
                            <td><strong class="workflow-request-id">#BRW-{{ str_pad($request->id, 6, '0', STR_PAD_LEFT) }}</strong><small>{{ \Illuminate\Support\Carbon::parse($request->created_at)->format('d M Y, H:i') }}</small></td>
                            <td><strong>{{ $request->borrower_name }}</strong><small>{{ $request->nim }} · {{ $request->email }}</small></td>
                            <td><strong>{{ $request->project_title }}</strong><small>{{ $request->serial_code ?: 'Unit belum ditentukan' }}</small></td>
                            <td>{{ \Illuminate\Support\Carbon::parse($request->due_date)->format('d M Y') }}</td>
                            <td><span class="workflow-status {{ strtolower($request->status) }}">{{ ucfirst($request->status) }}</span></td>
                            <td class="workflow-actions">
                                @if($request->status === 'pending')
                                    <form method="POST" action="{{ route('admin.approvals.borrowings.approve', $request->id) }}">@csrf<button class="workflow-button approve-button" type="submit" onclick="return confirm('Setujui pengajuan ini dan terbitkan QR?')">✓ Setujui &amp; Terbitkan QR</button></form>
                                    <form method="POST" action="{{ route('admin.approvals.borrowings.reject', $request->id) }}">@csrf<button class="workflow-button reject-button" type="submit" onclick="return confirm('Tolak pengajuan ini?')">Tolak</button></form>
                                @elseif($request->qr_token)
                                    <span class="workflow-issued">✓ QR diterbitkan</span>
                                @else
                                    <span class="workflow-muted">Tidak ada tindakan</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>
@endsection
