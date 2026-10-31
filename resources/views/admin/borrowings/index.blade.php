@extends('layouts.admin')
@section('title', 'Manajemen Peminjaman | Sawangun IoT Hub')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-borrowings.css') }}?v=1">
@endpush
@section('content')
<div class="br-page">
    <div class="br-breadcrumb"><span>Admin Panel</span><b>/</b><strong>Borrowings</strong></div>
    @if(session('success'))<div class="br-alert">✓ {{ session('success') }}</div>@endif
    <section class="br-heading">
        <div><p class="br-eyebrow">OPERASIONAL LAB / ADMIN</p><h1>Manajemen Peminjaman</h1><p>Pantau pengajuan peminjaman hardware, cek jadwal pengembalian, dan bantu setiap kit kembali ke tempatnya.</p></div>
        <div class="br-heading-actions"><a class="br-secondary" href="#borrowing-list">↓ Lihat daftar</a><a class="br-primary" href="{{ route('admin.borrowings.create') }}">＋ Formulir Pinjam Baru</a></div>
    </section>
    <section class="br-overview">
        <div class="br-overview-copy"><span class="br-live"><i></i> LAB OPERATIONS</span><h2>Semua kit, tetap terpantau.</h2><p>Ringkasan peminjaman untuk membantu admin memeriksa barang yang sedang digunakan, menunggu persetujuan, atau perlu ditindaklanjuti.</p><div class="br-overview-foot"><span>▦ {{ $total }} data peminjaman</span><span>◷ Diperbarui {{ now()->format('d M Y') }}</span></div><div class="br-graphic"><span class="br-ring ring-one"></span><span class="br-ring ring-two"></span><span class="br-chip">⌘</span><span class="br-dot dot-one"></span><span class="br-dot dot-two"></span></div></div>
        <div class="br-overview-status"><div class="br-status-head"><span>STATUS OPERASIONAL</span><small>DATA SESSION</small></div><div class="br-status-line"><span><i class="status-dot green"></i> Sedang dipinjam</span><b>{{ $active }}</b></div><div class="br-status-line"><span><i class="status-dot amber"></i> Menunggu persetujuan</span><b>{{ $pending }}</b></div><div class="br-status-line"><span><i class="status-dot red"></i> Terlambat</span><b>{{ $overdue }}</b></div><div class="br-status-note">Perubahan status bisa dilakukan langsung dari tabel. Data masih berupa contoh dan belum terhubung ke database.</div></div>
    </section>
    <section class="br-metrics">
        <article><div><small>TOTAL PEMINJAMAN</small><span class="br-metric-icon">⇄</span></div><strong>{{ $total }}</strong><p>Seluruh data di workspace ini</p></article>
        <article><div><small>SEDANG DIPINJAM</small><span class="br-metric-icon mint">↗</span></div><strong>{{ $active }}</strong><p>Hardware berada di tangan peminjam</p></article>
        <article><div><small>PERLU PERSETUJUAN</small><span class="br-metric-icon amber">◷</span></div><strong>{{ $pending }}</strong><p>Periksa detail sebelum disetujui</p></article>
        <article><div><small>TERLAMBAT</small><span class="br-metric-icon rose">!</span></div><strong>{{ $overdue }}</strong><p>Perlu tindak lanjut dari admin</p></article>
    </section>
    <section class="br-workflow">
        <div class="br-workflow-title"><span class="br-small-icon">⌘</span><div><h2>Alur peminjaman hardware</h2><p>Langkah singkat dari pengajuan sampai barang kembali.</p></div><span class="br-workflow-badge">PANDUAN ADMIN</span></div>
        <div class="br-steps"><div><span>01</span><b>Periksa pengajuan</b><small>Data peminjam & kebutuhan</small></div><i>→</i><div><span>02</span><b>Setujui peminjaman</b><small>Pastikan stok dan loker</small></div><i>→</i><div><span>03</span><b>Serahkan kit</b><small>Catat lokasi pengambilan</small></div><i>→</i><div><span>04</span><b>Konfirmasi kembali</b><small>Cek komponen lengkap</small></div></div>
    </section>
    <section class="br-table-section" id="borrowing-list">
        <div class="br-list-head"><div><h2>Daftar peminjaman</h2><p>Kelola status dan pantau batas waktu pengembalian.</p></div><span class="br-list-count">{{ $borrowings->count() }} data ditampilkan</span></div>
        <form class="br-filters" method="GET" action="{{ route('admin.borrowings.index') }}">
            <label class="br-filter-search"><span>⌕</span><input type="search" name="q" value="{{ $q }}" placeholder="Cari nama, NIM, proyek, atau kode..."></label>
            <label><span class="sr-only">Status</span><select name="status"><option value="">Semua status</option>@foreach(['pending'=>'Menunggu persetujuan','waiting'=>'Menunggu pengambilan','active'=>'Sedang dipinjam','overdue'=>'Terlambat','returned'=>'Dikembalikan','rejected'=>'Ditolak'] as $key=>$label)<option value="{{ $key }}" @selected($status===$key)>{{ $label }}</option>@endforeach</select></label>
            <button type="submit" class="br-filter-button">Terapkan filter</button>
            @if($q || $status)<a class="br-reset" href="{{ route('admin.borrowings.index') }}">Reset</a>@endif
        </form>
        <div class="br-table-wrap"><table class="br-table"><thead><tr><th>ID PEMINJAMAN</th><th>PEMINJAM</th><th>PROYEK & KOMPONEN</th><th>LOKASI LOKER</th><th>DURASI / JATUH TEMPO</th><th>STATUS</th><th>AKSI ADMIN</th></tr></thead><tbody>
        @forelse($borrowings as $row)
        <tr class="{{ $row['status']==='overdue' ? 'row-overdue' : '' }}">
            <td><strong class="br-code">{{ $row['code'] }}</strong><small>Token #{{ str_pad((string)$row['id'],4,'0',STR_PAD_LEFT) }}</small></td>
            <td><div class="br-person"><span class="br-avatar">{{ strtoupper(substr($row['name'],0,1)) }}</span><span><b>{{ $row['name'] }}</b><small>NIM {{ $row['nim'] }}</small><small>{{ $row['program'] }}</small></span></div></td>
            <td><b class="br-project-name">{{ $row['project'] }}</b><small>{{ $row['items'] }}</small></td>
            <td><span class="br-locker">{{ $row['locker'] }}</span><small>Smart Locker</small></td>
            <td><b>{{ $row['duration'] }} hari</b><small>Jatuh tempo {{ \Carbon\Carbon::parse($row['due'])->format('d M Y') }}</small></td>
            <td><span class="br-state {{ $row['status'] }}">{{ ['pending'=>'Menunggu','waiting'=>'Siap diambil','active'=>'Sedang dipinjam','overdue'=>'Terlambat','returned'=>'Dikembalikan','rejected'=>'Ditolak'][$row['status']] ?? ucfirst($row['status']) }}</span></td>
            <td><div class="br-actions">
                @if(in_array($row['status'], ['pending','waiting']))
                    <form method="POST" action="{{ route('admin.borrowings.status',$row['id']) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="{{ $row['status']==='pending' ? 'waiting' : 'active' }}"><button class="br-action-approve" type="submit">{{ $row['status']==='pending' ? 'Setujui' : 'Tandai dipinjam' }}</button></form>
                    <form method="POST" action="{{ route('admin.borrowings.status',$row['id']) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="rejected"><button class="br-action-reject" type="submit" title="Tolak">Tolak</button></form>
                @elseif(in_array($row['status'], ['active','overdue']))
                    <form method="POST" action="{{ route('admin.borrowings.status',$row['id']) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="returned"><button class="br-action-return" type="submit">Konfirmasi kembali</button></form>
                @else
                    <span class="br-action-done">Selesai</span>
                @endif
            </div></td>
        </tr>
        @empty
        <tr><td colspan="7"><div class="br-empty"><h3>Tidak ada data yang cocok</h3><p>Coba kata kunci lain atau reset filter untuk melihat semua peminjaman.</p></div></td></tr>
        @endforelse
        </tbody></table></div>
        <div class="br-table-footer"><span>Menampilkan {{ $borrowings->count() }} dari {{ $total }} data demo</span><span>Data disimpan sementara melalui session</span></div>
    </section>
    <p class="br-disclaimer">Workspace demo · Belum memakai database. Data dapat kembali ke contoh awal saat session berakhir.</p>
</div>
@endsection
