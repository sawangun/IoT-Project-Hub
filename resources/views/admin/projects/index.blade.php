@extends('layouts.admin')

@section('title', 'Manajemen Proyek IoT | Sawangun IoT Hub')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/projects-management.css') }}?v=2">
<link rel="stylesheet" href="{{ asset('css/admin-projects.css') }}?v=1">
@endpush

@section('content')
<div class="pm-page">
    <div class="pm-breadcrumb"><span>Admin</span><b>/</b><strong>Projects</strong></div>
    @if(session('success')) <div class="pm-alert success">✓ {{ session('success') }}</div> @endif
    <section class="pm-heading">
        <div><p class="pm-eyebrow">RUANG KERJA ADMIN</p><h1>Manajemen Proyek IoT Lab</h1><p>Atur proyek yang tampil di katalog, rapikan detailnya, dan siapkan publikasi tanpa perlu mengutak-atik kode satu per satu.</p></div>
        <div class="pm-heading-actions"><a class="pm-export" href="#project-list">Lihat daftar ↓</a><a class="pm-primary" href="{{ route('admin.projects.create') }}">＋ Tambah Proyek Baru</a></div>
    </section>

    <section class="pm-hero">
        <div class="pm-hero-art"><span class="pm-live"><i></i> WORKSPACE PROYEK</span><h2>Ide bagus layak punya tempat.</h2><p class="pm-hero-copy">Satu tempat untuk mengelola proyek, hardware, dan kesiapan publikasi tim lab.</p><div class="pm-hero-meta"><span>▦ {{ $totalProjects }} proyek terdaftar</span><span>◷ Diperbarui {{ now()->format('d M Y') }}</span></div><div class="pm-orbit"></div><div class="pm-orbit pm-orbit-two"></div><div class="pm-core">⌘</div><span class="pm-node node-a">·</span><span class="pm-node node-b">+</span><span class="pm-node node-c">·</span><span class="pm-node node-d">+</span></div>
        <div class="pm-hero-side"><div class="pm-hero-side-head"><span>RINGKASAN PUBLIKASI</span><small>DATA SEMENTARA</small></div><div class="pm-progress-row"><span>Sudah dipublikasikan</span><b>{{ $publishedProjects }}</b></div><div class="pm-progress"><i style="width:{{ $totalProjects ? ($publishedProjects / $totalProjects) * 100 : 0 }}%"></i></div><div class="pm-progress-row"><span>Masih berupa draft</span><b>{{ $draftProjects }}</b></div><div class="pm-progress"><i class="yellow" style="width:{{ $totalProjects ? ($draftProjects / $totalProjects) * 100 : 0 }}%"></i></div><div class="pm-telemetry"><span>● Semua perubahan bersifat lokal</span><small>Belum tersambung ke database. Data demo disimpan dalam session selama sesi aktif.</small></div><div class="pm-sync"><span>Status penyimpanan</span><b>Session mode</b></div></div>
    </section>

    <section class="pm-metrics">
        <article><div><small>TOTAL PROYEK</small><span class="metric-icon">◇</span></div><strong>{{ $totalProjects }}</strong><p>Termasuk draft dan publikasi</p></article>
        <article><div><small>SUDAH TAYANG</small><span class="metric-icon teal">↗</span></div><strong>{{ $publishedProjects }}</strong><p><i></i> Terlihat di katalog admin</p></article>
        <article><div><small>DRAFT</small><span class="metric-icon violet">✎</span></div><strong>{{ $draftProjects }}</strong><p>Belum dipublikasikan</p></article>
        <article><div><small>KATEGORI</small><span class="metric-icon blue">⌘</span></div><strong>{{ $categories->count() }}</strong><p>Bidang proyek berbeda</p></article>
    </section>

    <form class="pm-filter-panel" action="{{ route('admin.projects.index') }}" method="GET" id="project-list">
        <label class="pm-filter-search"><span>⌕</span><input type="search" name="q" value="{{ $q }}" placeholder="Cari nama proyek, tim, atau hardware..."></label>
        <label>Bidang <select name="category"><option value="">Semua bidang</option>@foreach($categories as $item)<option value="{{ $item }}" @selected($category === $item)>{{ $item }}</option>@endforeach</select></label>
        <label>Status <select name="status"><option value="">Semua status</option><option value="published" @selected($status === 'published')>Dipublikasikan</option><option value="draft" @selected($status === 'draft')>Draft</option></select></label>
        <button class="pm-filter-submit" type="submit">Terapkan</button>
        @if($q || $category || $status)<a class="pm-reset" href="{{ route('admin.projects.index') }}">Reset filter</a>@endif
    </form>

    <div class="pm-list-heading"><div><h2>Daftar proyek</h2><p>{{ $projects->count() }} proyek ditemukan. Pilih salah satu untuk mengubah detailnya.</p></div><span class="pm-update"><i></i> Tampilan admin</span></div>
    <section class="pm-project-grid">
        @forelse($projects as $project)
        <article class="pm-project-card">
            <div class="pm-card-visual {{ ['green','blue','purple','orange','pink'][$loop->index % 5] }}">
                <span class="pm-card-category">{{ $project['category'] }}</span><span class="pm-card-icon">{{ ['⌘','⌁','⌬','⌖','◉'][$loop->index % 5] }}</span><span class="pm-card-orbit"></span><span class="pm-card-orbit second"></span><span class="pm-card-code">SAWANGUN / IOT</span>
            </div>
            <div class="pm-card-body"><div class="pm-card-tags"><span>{{ $project['year'] }} · {{ $project['level'] }}</span><span class="pm-state {{ $project['status'] }}">{{ $project['status'] === 'published' ? '● Dipublikasikan' : '● Draft' }}</span></div><h3>{{ $project['title'] }}</h3><p>{{ $project['description'] }}</p><div class="pm-card-specs"><span>♙ {{ $project['team'] }}</span><span>▣ {{ $project['locker'] }}</span></div><small class="pm-modules-label">HARDWARE / KOMPONEN</small><div class="pm-tags">@foreach(explode(',', $project['hardware']) as $tag)<span>{{ trim($tag) }}</span>@endforeach</div>
                <div class="pm-card-footer"><a class="pm-detail" href="{{ route('admin.projects.edit', $project['id']) }}">Edit detail ↗</a><form method="POST" action="{{ route('admin.projects.destroy', $project['id']) }}" onsubmit="return confirm('Hapus proyek ini dari daftar sementara?')">@csrf @method('DELETE')<button class="pm-icon-button" type="submit" aria-label="Hapus proyek" title="Hapus proyek">⌫</button></form></div>
            </div>
        </article>
        @empty
        <div class="pm-empty"><span>⌕</span><h3>Belum ada proyek yang cocok</h3><p>Coba kata kunci atau filter lain. Kalau proyek baru, kamu bisa menambahkannya sekarang.</p><a class="pm-primary" href="{{ route('admin.projects.create') }}">＋ Tambah proyek</a></div>
        @endforelse
    </section>
    <p class="pm-demo-note">Catatan: proyek dan perubahan pada halaman ini menggunakan session, bukan database. Data dapat kembali ke contoh awal ketika session berakhir.</p>
</div>
@endsection
