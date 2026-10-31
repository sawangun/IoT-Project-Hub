@extends('layouts.app')

@section('title', 'IoT Project Hub — Proyek dan Smart Locker')

@section('content')
<section class="hero">
    <div class="container hero-grid">
        <div class="hero-copy">
            <span class="eyebrow"><i></i> EKOSISTEM IOT KAMPUS</span>
            <h1>Ide Besar Dimulai dari <span>Proyek Kecil.</span></h1>
            <p class="hero-lead">Jelajahi proyek IoT, temukan modul yang kamu butuhkan, dan mulai bangun prototipe untuk tugas akhir atau riset kampusmu.</p>
            <div class="hero-actions">
                <a href="#projects" class="btn btn-primary">Jelajahi Proyek <span>↗</span></a>
                <a href="#steps" class="btn btn-light">Cara Peminjaman</a>
            </div>
            <div class="metrics"><div><strong>120+</strong><small>Referensi Proyek</small></div><div><strong>48</strong><small>Kompartemen Locker</small></div><div><strong>12</strong><small>Lab Terkoneksi</small></div></div>
        </div>
        <div class="hero-visual">
            <img src="https://images.unsplash.com/photo-1581092921461-eab62e97a780?auto=format&fit=crop&w=1100&q=85" alt="Prototipe dan perangkat elektronik di laboratorium">
            <div class="floating-card"><span class="float-icon">⌁</span><div><strong>Smart Locker</strong><small>Akses mandiri · 24/7</small></div><span class="live-dot"></span></div>
            <div class="image-caption"><span>LAB INFRASTRUCTURE</span><strong>Build. Test. Make it real.</strong></div>
        </div>
    </div>
    <div class="container locker-strip">
        <div class="locker-intro"><span class="locker-symbol">▤</span><div><strong>Ketersediaan Smart Locker</strong><small>Contoh status kompartemen</small></div></div>
        <div class="locker-list">
            @foreach ($lockerStats as $locker)
            <div class="locker-item"><div><strong>{{ $locker['name'] }}</strong><span>{{ $locker['available'] }}/{{ $locker['total'] }} tersedia</span></div><div class="progress"><i style="width: {{ ($locker['available'] / $locker['total']) * 100 }}%"></i></div></div>
            @endforeach
        </div>
    </div>
</section>

<section class="section container" id="benefits">
    <div class="section-head centered"><span class="kicker">DIBUAT UNTUK PARA BUILDER</span><h2>Semua yang Dibutuhkan untuk Mulai</h2><p>Dari mencari inspirasi sampai menyiapkan perangkat, semua informasi ada di satu tempat.</p></div>
    <div class="benefit-grid">
        <article class="benefit-card"><span class="benefit-icon">◷</span><h3>Akses Lebih Fleksibel</h3><p>Informasi peminjaman tersedia kapan saja. Tidak perlu mulai dari chat yang tercecer.</p></article>
        <article class="benefit-card"><span class="benefit-icon violet">⌘</span><h3>Kit dan Komponen</h3><p>Kenali board, sensor, dan modul yang sesuai dengan kebutuhan prototipemu.</p></article>
        <article class="benefit-card"><span class="benefit-icon teal">▧</span><h3>Referensi Proyek</h3><p>Temukan inspirasi untuk eksperimen, tugas akhir, dan kolaborasi riset.</p></article>
        <article class="benefit-card"><span class="benefit-icon orange">◎</span><h3>Alur yang Jelas</h3><p>Pelajari tahapan pengajuan sebelum menggunakan fitur peminjaman.</p></article>
    </div>
</section>

<section class="section projects-section" id="projects">
    <div class="container">
        <div class="section-title-row"><div><span class="kicker">PROJECT MARKETPLACE</span><h2>Proyek Pilihan</h2><p>Beberapa ide untuk memulai eksplorasi IoT-mu.</p></div><a class="view-all" href="{{ route('login') }}">Lihat katalog lengkap ↗</a></div>
        <div class="project-grid">
            @forelse ($projects as $project)
            <article class="project-card">
                <div class="project-image">
                    @if (!empty($project->image_url))
                        <img src="{{ $project->image_url }}" alt="{{ $project->title }}" loading="lazy">
                    @else
                        <div class="project-image-placeholder" aria-hidden="true">IoT</div>
                    @endif
                    <span>{{ ucfirst($project->difficulty ?? 'Proyek IoT') }}</span>
                </div>
                <div class="project-body">
                    <h3>{{ $project->title }}</h3>
                    <p>{{ \Illuminate\Support\Str::limit($project->description ?: 'Belum ada deskripsi untuk proyek ini.', 120) }}</p>
                    <a href="{{ route('login') }}" class="project-link">Masuk untuk melihat detail <span>→</span></a>
                </div>
            </article>
            @empty
            <p>Belum ada proyek yang tersedia.</p>
            @endforelse
        </div>
    </div>
</section>

<section class="section container" id="steps">
    <div class="section-head centered"><span class="kicker">GAMPANG DAN TERARAH</span><h2>Mulai dalam 3 Langkah</h2><p>Alur singkat untuk pengguna yang ingin memanfaatkan fasilitas lab.</p></div>
    <div class="steps-grid">
        <article><span class="step-number">01</span><h3>Temukan Perangkat</h3><p>Jelajahi katalog dan cari kit atau sensor yang sesuai.</p></article>
        <article><span class="step-number">02</span><h3>Masuk ke Akun</h3><p>Login atau buat akun untuk mengakses fitur peminjaman.</p></article>
        <article><span class="step-number">03</span><h3>Ajukan Peminjaman</h3><p>Ikuti prosedur lab dan pantau status pengajuanmu.</p></article>
    </div>
</section>

<section class="cta"><div class="container cta-inner"><div><span class="kicker">PUNYA IDE YANG MAU DIWUJUDKAN?</span><h2>Yuk, mulai proyek IoT pertamamu.</h2><p>Buat akun agar kamu bisa melanjutkan ke fitur proyek dan peminjaman.</p></div><a class="btn btn-white" href="{{ route('register') }}">Daftar Akun Mahasiswa ↗</a></div></section>
@endsection
