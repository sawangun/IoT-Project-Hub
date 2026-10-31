<footer class="site-footer" id="about">
    <div class="container footer-grid {{ auth()->check() ? 'footer-grid-auth' : 'footer-grid-guest' }}">
        <div class="footer-about">
            <a class="brand" href="{{ auth()->check() && auth()->user()->role === 'pengguna' ? route('dashboard') : route('home') }}"><span class="brand-mark">IoT</span><span class="brand-name"><strong>IoT Project Hub</strong><small>PROJECT PORTAL</small></span></a>
            <p>Pusat eksplorasi proyek embedded system dan perangkat cerdas untuk mahasiswa dan periset kampus.</p>
            <div class="online"><span></span> Smart Locker System · Online</div>
        </div>
        <div class="footer-col">
            <h3>Navigasi</h3>
            @if(auth()->check() && auth()->user()->role === 'pengguna')
                <a href="{{ route('dashboard') }}">Beranda</a>
                <a href="{{ route('projects.index') }}">Eksplorasi Proyek</a>
                <a href="{{ route('catalog.index') }}">Kit & Komponen</a>
                <a href="{{ route('borrow.index') }}">Peminjaman Saya</a>
                <a href="{{ route('user.history') }}">Riwayat Peminjaman</a>
                <a href="{{ route('borrow.index') }}#panduan">Panduan Peminjaman</a>
                <a href="{{ route('about') }}">Tentang Kami</a>
            @else
                <a href="{{ route('home') }}">Beranda</a>
                <a href="{{ route('home') }}#projects">Katalog Proyek</a>
                <a href="{{ route('home') }}#steps">Cara Peminjaman</a>
                <a href="{{ route('home') }}#about">Tentang Kami</a>
            @endif
        </div>
        <div class="footer-col"><h3>Eksplorasi</h3>
            @if(auth()->check() && auth()->user()->role === 'pengguna')
                <a href="{{ route('projects.index') }}">Smart Agriculture</a><a href="{{ route('projects.index') }}">Telemetri</a><a href="{{ route('projects.index') }}">Computer Vision</a>
            @else
                <a href="{{ route('home') }}#projects">Smart Agriculture</a><a href="{{ route('home') }}#projects">Telemetri</a><a href="{{ route('home') }}#projects">Computer Vision</a>
            @endif
        </div>
        @guest<div class="footer-col"><h3>Akun</h3><a href="{{ route('login') }}">Masuk</a><a href="{{ route('register') }}">Daftar Mahasiswa</a></div>@endguest
    </div>
    <div class="container footer-bottom"><span>© {{ date('Y') }} IoT Project Hub</span><span>Dibuat untuk mendukung riset dan inovasi kampus</span></div>
</footer>
