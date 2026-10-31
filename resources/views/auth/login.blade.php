@extends('layouts.app')

@section('title', 'Masuk — IoT Project Hub')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endpush

@section('content')
<section class="login-page">
    <div class="login-topline">
        <a class="login-back" href="{{ route('home') }}">← Kembali ke Beranda</a>
        <span class="login-status"><i></i> PORTAL RISET & EKOSISTEM IoT</span>
        <span class="login-system">PDDikti SSO Node: Online</span>
    </div>

    <div class="login-layout">
        <aside class="login-aside">
            <div class="login-aside-meta">
                <span class="login-chip">NEXUS NODE</span>
                <span>SEC-ID: AUTH-2025</span>
                <span class="login-aside-dot">✳</span>
            </div>
            <h1>Eksplorasi IoT dimulai dari sini.</h1>
            <p class="login-aside-copy">Masuk ke ruang kolaborasi riset, akses perangkat laboratorium, dan kelola proyek open-hardware kampus dalam satu portal.</p>

            <div class="login-visual" role="img" aria-label="Ilustrasi perangkat IoT dan papan sirkuit">
                <div class="login-circuit circuit-one"></div>
                <div class="login-circuit circuit-two"></div>
                <div class="login-board"><span>IoT</span><i></i><b></b><em></em></div>
                <div class="login-visual-caption"><span>● SMART LAB NETWORK</span><span>ESP32 / LoRaWAN</span></div>
            </div>

            <div class="login-benefits">
                <div class="login-benefit"><span class="benefit-icon">✣</span><div><strong>Akses Smart Locker</strong><small>Kelola peminjaman instrumen dan modul praktikum.</small></div></div>
                <div class="login-benefit"><span class="benefit-icon">ϟ</span><div><strong>Kolaborasi Riset</strong><small>Temukan proyek dan kolaborator lintas program studi.</small></div></div>
                <div class="login-benefit"><span class="benefit-icon">⌘</span><div><strong>Portofolio Terintegrasi</strong><small>Dokumentasikan eksperimen dan publikasi proyekmu.</small></div></div>
            </div>
            <div class="login-aside-foot"><span>BUILT FOR CAMPUS INNOVATORS</span><span>v2.8.4</span></div>
        </aside>

        <section class="login-panel">
            <div class="login-panel-head">
                <span class="login-eyebrow"><i></i> AUTENTIKASI PENGGUNA</span>
                <h2>Selamat datang kembali</h2>
                <p>Masuk ke akun IoT Project Hub untuk melanjutkan aktivitas riset Anda.</p>
            </div>

            <div class="login-sso">
                <span class="login-sso-icon">U</span>
                <div><strong>Masuk cepat via SSO Universitas</strong><small>Gunakan akun kampus untuk akses yang lebih praktis.</small></div>
                <span class="login-sso-badge">SEGERA</span>
            </div>

            <div class="login-divider"><span>ATAU MASUK DENGAN EMAIL</span></div>

            @if (session('status'))
                <div class="login-success" role="status">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="login-errors" role="alert">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form class="login-form" method="POST" action="{{ route('login.store') }}">
                @csrf
                <label for="email">Email kampus <span>*</span></label>
                <div class="login-input-wrap"><span class="login-input-icon">✉</span><input id="email" name="email" type="email" autocomplete="username" placeholder="nama@mahasiswa.kampus.ac.id" value="{{ old('email') }}" required></div>

                <div class="login-label-row"><label for="password">Kata sandi <span>*</span></label><a href="#" onclick="event.preventDefault(); alert('Fitur reset kata sandi belum diaktifkan.');">Lupa kata sandi?</a></div>
                <div class="login-input-wrap"><span class="login-input-icon">⌑</span><input id="password" name="password" type="password" autocomplete="current-password" placeholder="Masukkan kata sandi" required><button class="login-show-password" type="button" aria-label="Tampilkan kata sandi" onclick="const p=document.getElementById('password'); p.type=p.type==='password'?'text':'password'; this.textContent=p.type==='password'?'◎':'◉';">◎</button></div>

                <label class="login-remember"><input type="checkbox" name="remember" value="1"><span>Ingat saya di perangkat ini</span></label>
                <button class="login-submit" type="submit">Masuk ke Akun <span>→</span></button>
            </form>

            <p class="login-register">Belum memiliki akun? <a href="{{ route('register') }}">Daftar akun baru</a></p>
            <div class="login-security"><span>▣</span><p><strong>Akses aman untuk komunitas kampus</strong><small>Jangan bagikan kata sandi Anda kepada siapa pun.</small></p></div>
            <a class="login-home-link" href="{{ route('home') }}">← Kembali ke beranda</a>
        </section>
    </div>
    <p class="login-copyright">© {{ date('Y') }} IoT Project Hub · Platform kolaborasi riset kampus</p>
</section>
@endsection
