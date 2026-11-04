
<header class="site-header {{ request()->routeIs('dashboard', 'projects.*', 'catalog.*', 'borrow.*', 'user.history', 'checkout.*', 'about', 'user.profile', 'projects.saved') ? 'app-header' : '' }}">
    <nav class="container nav-wrap" aria-label="Navigasi utama">

        {{-- Brand --}}
        <a class="brand"
           href="{{ request()->routeIs('dashboard', 'projects.*', 'catalog.*', 'borrow.*', 'user.history', 'checkout.*', 'about', 'user.profile', 'projects.saved') ? route('dashboard') : route('home') }}">
            <span class="brand-mark">IoT</span>
            <span class="brand-name">
                <strong>IoT Hub</strong>
                <small>PROJECT PORTAL</small>
            </span>
        </a>

        {{-- Tombol burger mobile --}}
        <button
            class="nav-toggle"
            id="navToggle"
            type="button"
            aria-label="Buka menu"
            aria-expanded="false"
            aria-controls="navMenu"
        >
            <span></span>
            <span></span>
            <span></span>
        </button>

        @if (request()->routeIs('dashboard', 'projects.*', 'catalog.*', 'borrow.*', 'user.history', 'checkout.*', 'about', 'user.profile', 'projects.saved'))

            {{-- NAVBAR PENGGUNA --}}
            <div class="nav-menu user-nav-menu" id="navMenu">

                <a href="{{ route('dashboard') }}"
                   class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    Beranda
                </a>

                <a href="{{ route('projects.index') }}"
                   class="nav-link {{ request()->routeIs('projects.*') ? 'active' : '' }}">
                    Eksplorasi Proyek
                </a>

                <a href="{{ route('catalog.index') }}" class="nav-link {{ request()->routeIs('catalog.*') ? 'active' : '' }}">Kit & Komponen</a>

                {{-- Semua aktivitas peminjaman dikumpulkan dalam satu dropdown. --}}
                <div class="nav-dropdown borrowing-dropdown">
                    <button
                        type="button"
                        class="nav-link dropdown-trigger {{ request()->routeIs('borrow.*', 'user.history') ? 'active' : '' }}"
                        aria-expanded="false"
                    >
                        Peminjaman
                        <span class="chevron">⌄</span>
                    </button>

                    <div class="dropdown-panel">
                        <a href="{{ route('borrow.index') }}">Peminjaman Berlangsung</a>
                        <a href="{{ route('user.history') }}">Riwayat Peminjaman</a>
                        <a href="{{ route('borrow.index') }}#panduan">Panduan Peminjaman</a>
                        <a href="{{ route('borrow.index', ['tab' => 'tersedia']) }}#available-units">Cari Perangkat</a>
                    </div>
                </div>

                {{-- Pencarian proyek --}}
                <form
                    class="nav-search"
                    id="globalSearchForm"
                    action="{{ route('projects.index') }}"
                    method="GET"
                    role="search"
                >
                    <span class="search-icon" aria-hidden="true">⌕</span>

                    <input
                        id="globalSearch"
                        name="q"
                        type="search"
                        value="{{ request('q') }}"
                        placeholder="Cari proyek..."
                        aria-label="Cari proyek"
                        autocomplete="off"
                    >

                    <kbd class="search-shortcut" id="searchShortcut">Ctrl K</kbd>
                </form>

                {{-- Notifikasi --}}
                <div class="nav-dropdown icon-dropdown">
                    <button
                        type="button"
                        class="nav-icon dropdown-trigger"
                        aria-label="Notifikasi"
                        aria-expanded="false"
                    >
                        ♧
                        <span class="notification-dot"></span>
                    </button>

                    <div class="dropdown-panel dropdown-right notification-panel">
                        <strong>Notifikasi</strong>
                        <p class="dropdown-empty">Belum ada notifikasi baru.</p>
                    </div>
                </div>

                {{-- Proyek tersimpan --}}
                <a
                    href="{{ route('projects.saved') }}"
                    class="nav-icon"
                    aria-label="Proyek disukai"
                    title="Proyek disukai"
                >
                    ♡
                </a>

                {{-- Profil pengguna --}}
                <div class="nav-dropdown profile-dropdown">
                    <button
                        type="button"
                        class="profile-trigger dropdown-trigger"
                        aria-expanded="false"
                        aria-label="Buka menu profil"
                    >
                        <span class="profile-avatar">{{ strtoupper(mb_substr(auth()->user()->name ?? 'P', 0, 1)) }}</span>

                        <span class="profile-copy">
                            <strong>{{ auth()->user()->name ?? 'Pengguna' }}</strong>
                            <small>Pengguna IoT Hub</small>
                        </span>

                        <span class="chevron">⌄</span>
                    </button>

                    <div class="dropdown-panel dropdown-right profile-panel">
                        <div class="profile-panel-heading">
                            <strong>{{ auth()->user()->name ?? 'Pengguna' }}</strong>
                            <small>Pengguna IoT Hub</small>
                        </div>

                        <a href="{{ route('user.profile') }}">Profil Saya</a>
                        <a href="{{ route('projects.saved') }}">Proyek Disukai</a>
                        <form method="POST" action="{{ route('logout') }}" class="dropdown-logout">@csrf<button type="submit">Keluar / Logout</button></form>
                    </div>
                </div>

            </div>

        @else

            {{-- NAVBAR PENGUNJUNG --}}
            <div class="nav-menu guest-nav-menu" id="navMenu">

                <a
                    href="{{ route('home') }}"
                    class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}"
                >
                    Beranda
                </a>

                <a href="{{ route('home') }}#projects" class="nav-link">
                    Jelajahi Proyek
                </a>

                <a href="{{ route('home') }}#benefits" class="nav-link">
                    Fasilitas
                </a>

                <a href="{{ route('home') }}#steps" class="nav-link">
                    Cara Peminjaman
                </a>

                <a href="{{ route('login') }}" class="nav-link nav-login">
                    Masuk
                </a>

                <a href="{{ route('register') }}" class="nav-cta">
                    Daftar Akun
                </a>

            </div>

        @endif

    </nav>
</header>

<script>
(function () {
    function initNavbar() {
        document.querySelectorAll('.site-header').forEach(function (header) {
            if (header.dataset.navInitialized === 'true') return;
            header.dataset.navInitialized = 'true';

            const menu = header.querySelector('.nav-menu');
            const toggle = header.querySelector('.nav-toggle');
            const dropdowns = header.querySelectorAll('.nav-dropdown');

            if (!menu || !toggle) return;

            function closeDropdowns(except = null) {
                dropdowns.forEach(function (dropdown) {
                    if (dropdown === except) return;

                    dropdown.classList.remove('is-open');

                    const button = dropdown.querySelector('.dropdown-trigger');

                    if (button) {
                        button.setAttribute('aria-expanded', 'false');
                    }
                });
            }

            function closeMobileMenu() {
                menu.classList.remove('is-open');
                toggle.setAttribute('aria-expanded', 'false');
                toggle.setAttribute('aria-label', 'Buka menu');
                closeDropdowns();
            }

            // Toggle menu mobile
            toggle.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();

                const isOpen = menu.classList.toggle('is-open');

                toggle.setAttribute('aria-expanded', String(isOpen));
                toggle.setAttribute(
                    'aria-label',
                    isOpen ? 'Tutup menu' : 'Buka menu'
                );

                if (!isOpen) closeDropdowns();
            });

            // Toggle dropdown
            dropdowns.forEach(function (dropdown) {
                const button = dropdown.querySelector('.dropdown-trigger');

                if (!button) return;

                button.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();

                    const wasOpen = dropdown.classList.contains('is-open');

                    closeDropdowns();

                    if (!wasOpen) {
                        dropdown.classList.add('is-open');
                        button.setAttribute('aria-expanded', 'true');
                    }
                });
            });

            // Klik di luar navbar
            document.addEventListener('click', function (event) {
                if (!header.contains(event.target)) {
                    closeDropdowns();
                    closeMobileMenu();
                }
            });

            // Tutup menu saat link diklik pada mobile
            menu.querySelectorAll('a').forEach(function (link) {
                link.addEventListener('click', function () {
                    if (window.innerWidth <= 760) {
                        closeMobileMenu();
                    }
                });
            });

            // Tutup dengan Escape
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeDropdowns();
                    closeMobileMenu();
                }
            });

            // Pintasan pencarian Ctrl+K / Cmd+K
            const search = header.querySelector('#globalSearch');
            const shortcut = header.querySelector('#searchShortcut');
            const isMac = /Mac|iPhone|iPad/.test(navigator.platform || '');

            if (shortcut && isMac) {
                shortcut.textContent = '⌘ K';
            }

            document.addEventListener('keydown', function (event) {
                if (
                    (event.ctrlKey || event.metaKey) &&
                    event.key.toLowerCase() === 'k' &&
                    search
                ) {
                    event.preventDefault();
                    search.focus();
                    search.select();
                }
            });

            // Jangan kirim pencarian kosong
            const form = header.querySelector('#globalSearchForm');

            if (form && search) {
                form.addEventListener('submit', function (event) {
                    if (!search.value.trim()) {
                        event.preventDefault();
                        search.focus();
                    }
                });
            }

            // Jika layar kembali ke desktop, reset status menu mobile
            window.addEventListener('resize', function () {
                if (window.innerWidth > 760) {
                    closeMobileMenu();
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initNavbar);
    } else {
        initNavbar();
    }
})();
</script>
