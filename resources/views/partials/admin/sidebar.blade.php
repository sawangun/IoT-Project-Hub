<aside class="admin-sidebar" id="adminSidebar">
    <a href="{{ route('admin.dashboard') }}" class="admin-brand">
        <span class="brand-symbol">S</span>
        <span><strong>Sawangun Digital</strong><small>IoT PROJECT HUB</small></span>
    </a>

    <div class="admin-label">ADMIN PANEL</div>
    <div class="sidebar-caption">MAIN</div>
    <nav class="admin-nav" aria-label="Navigasi admin">
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><span>▦</span>Dashboard</a>
        <a href="{{ route('admin.projects.index') }}" class="{{ request()->routeIs('admin.projects.*') ? 'active' : '' }}"><span>◇</span>Projects</a>
        <a href="{{ route('admin.borrowings.index') }}" class="{{ request()->routeIs('admin.borrowings.*') ? 'active' : '' }}"><span>⇄</span>Borrowings</a>
        <a href="{{ route('admin.inventory.lockers') }}" class="{{ request()->routeIs('admin.inventory.lockers') ? 'active' : '' }}"><span>▣</span>Smart Locker</a>
        <a href="{{ route('admin.inventory.kits') }}" class="{{ request()->routeIs('admin.inventory.kits', 'admin.inventory.kit-*', 'admin.inventory.component-*') ? 'active' : '' }}"><span>⌘</span>Kits & Components</a>
        <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}"><span>♙</span>Users</a>
    </nav>

    <div class="sidebar-caption">MANAGEMENT</div>
    <nav class="admin-nav">
        <a href="{{ route('admin.approvals.projects') }}" class="{{ request()->routeIs('admin.approvals.projects') ? 'active' : '' }}"><span>◉</span>Project Approval <em>3</em></a>
        <a href="{{ route('admin.approvals.borrowings') }}" class="{{ request()->routeIs('admin.approvals.borrowings') ? 'active' : '' }}"><span>⇥</span>Borrowing Approval <em>7</em></a>
        <a href="{{ route('admin.qr.show') }}" class="{{ request()->routeIs('admin.qr.*') ? 'active' : '' }}"><span>▦</span>QR Codes</a>
        <a href="{{ route('admin.reports') }}" class="{{ request()->routeIs('admin.reports') ? 'active' : '' }}"><span>▤</span>Reports</a>
        <a href="{{ route('admin.activity') }}" class="{{ request()->routeIs('admin.activity') ? 'active' : '' }}"><span>◷</span>Activity Log</a>
    </nav>

    <div class="sidebar-bottom">
        <a href="{{ route('admin.settings') }}" class="{{ request()->routeIs('admin.settings') ? 'active' : '' }}"><span>⚙</span>Settings</a>
        <a href="{{ route('admin.help') }}" class="{{ request()->routeIs('admin.help') ? 'active' : '' }}"><span>?</span>Help Center</a>
        <a href="{{ route('admin.profile') }}" class="{{ request()->routeIs('admin.profile') ? 'active' : '' }}"><span>♙</span>Admin Profile</a>
    </div>
</aside>
