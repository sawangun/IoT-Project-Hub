<header class="admin-topbar admin-topbar-unified">
    <div class="mobile-brand">
        <button class="menu-toggle" type="button" onclick="document.getElementById('adminSidebar')?.classList.toggle('sidebar-open')" aria-label="Buka menu">☰</button>
        <strong>Sawangun Admin</strong>
    </div>

    <label class="topbar-search admin-global-search" aria-label="Pencarian admin">
        <span aria-hidden="true">⌕</span>
        <input type="search" placeholder="Cari project, user, peminjaman..." autocomplete="off">
        <kbd>Ctrl K</kbd>
    </label>

    <div class="admin-header-actions">
        <button class="admin-notification" type="button" aria-label="Notifikasi">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg>
            <i></i>
        </button>

        <div class="admin-profile-menu" data-admin-profile-menu>
            <button class="admin-profile-trigger" type="button" aria-haspopup="true" aria-expanded="false">
                <span class="user-avatar admin-avatar">AD</span>
                <span class="user-meta admin-profile-copy"><strong>{{ auth()->user()->name ?? 'Admin' }}</strong><small>Administrator</small></span>
                <span class="admin-profile-chevron">⌄</span>
            </button>
            <div class="admin-profile-dropdown" role="menu">
                <div class="admin-profile-dropdown-head">
                    <span class="user-avatar admin-avatar small">AD</span>
                    <span><strong>{{ auth()->user()->name ?? 'Admin' }}</strong><small>Administrator</small></span>
                </div>
                <a href="{{ route('admin.profile') }}" role="menuitem"><span>♙</span><div><strong>Detail Profil</strong><small>Lihat profil dan hak akses admin</small></div></a>
                <a href="{{ route('admin.settings') }}" role="menuitem"><span>⚙</span><div><strong>Pengaturan</strong><small>Konfigurasi sistem IoT Hub</small></div></a>
                <a href="{{ route('admin.help') }}" role="menuitem"><span>?</span><div><strong>Pusat Bantuan</strong><small>Dokumentasi dan bantuan teknis</small></div></a>
                <form method="POST" action="{{ route('logout') }}" style="margin:0">@csrf<button type="submit" role="menuitem" style="width:100%;border:0;background:transparent;text-align:left;cursor:pointer;padding:12px 16px">↪ &nbsp; <strong>Keluar</strong></button></form>
            </div>
        </div>
    </div>
</header>
