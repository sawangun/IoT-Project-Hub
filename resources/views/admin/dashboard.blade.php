@extends('layouts.admin')

@section('title', 'Dashboard Admin | Sawangun IoT Hub')

@section('content')
<section class="welcome-row">
    <div>
        <p class="eyebrow">OVERVIEW</p>
        <h1>Selamat pagi, {{ $adminName }} 👋</h1>
        <p class="welcome-subtitle">Pantau dan kelola aktivitas IoT Project Hub hari ini.</p>
    </div>
    <div class="date-chip">◷ {{ now()->translatedFormat('l, d F Y') }}</div>
</section>

<section class="stats-grid" aria-label="Statistik dashboard">
    <article class="stat-card">
        <div class="stat-head"><span>Total Projects</span><i class="stat-icon blue">◇</i></div>
        <strong>{{ $totalProjects }}</strong>
        <small class="stat-note positive">↗ Data proyek pada katalog</small>
    </article>

    <article class="stat-card">
        <div class="stat-head"><span>Active Borrowings</span><i class="stat-icon violet">⇄</i></div>
        <strong>{{ $activeBorrowings }}</strong>
        <small class="stat-note">Peminjaman sedang berlangsung</small>
    </article>

    <article class="stat-card">
        <div class="stat-head"><span>Pending Requests</span><i class="stat-icon orange">◷</i></div>
        <strong>{{ $pendingBorrowings }}</strong>
        <small class="stat-note warning">Perlu ditinjau admin</small>
    </article>

    <article class="stat-card">
        <div class="stat-head"><span>Available Lockers</span><i class="stat-icon green">▣</i></div>
        <strong>{{ $lockerStats['available'] }}<small> / {{ $lockerStats['total'] }}</small></strong>
        <small class="stat-note positive">{{ $lockerStats['occupied'] }} kompartemen terisi</small>
    </article>
</section>

<section class="process-panel panel">
    <div class="section-heading">
        <div>
            <h2>Alur Peminjaman & Smart Locker IoT</h2>
            <p>Proses peminjaman perangkat dari pengajuan hingga pengambilan</p>
        </div>
        <span class="info-chip">ⓘ Alur sistem</span>
    </div>

    <div class="process-steps">
        @foreach ([['Pengajuan User', 'Form peminjaman'], ['Review Admin', 'Verifikasi kelayakan'], ['Approve & QR', 'Generate token'], ['Ke Smart Locker', 'Kunjungi lokasi'], ['Scanner Baca QR', 'QR code dipindai'], ['Pintu Terbuka', 'Solenoid aktif'], ['Status Active', 'Masa pinjam dimulai']] as $index => $step)
            <div class="process-step">
                <span class="step-number">{{ sprintf('%02d', $index + 1) }}</span>
                <strong>{{ $step[0] }}</strong>
                <small>{{ $step[1] }}</small>
            </div>
        @endforeach
    </div>
</section>

<section class="overview-grid">
    <article class="panel chart-panel">
        <div class="section-heading">
            <div>
                <h2>Borrowing Overview</h2>
                <p>Aktivitas peminjaman dalam 7 hari terakhir</p>
            </div>
            <span class="period-chip">7 hari terakhir</span>
        </div>

        <div class="chart-area">
            @php($chartMax = max(1, max(array_column($weeklyChart, 'total'))))
            @foreach ($weeklyChart as $day)
                <div class="chart-column">
                    <div class="bar-track">
                        <div class="bar-fill" style="height: {{ max(5, ($day['total'] / $chartMax) * 100) }}%" title="{{ $day['total'] }} peminjaman"></div>
                    </div>
                    <small>{{ $day['label'] }}</small>
                    <b>{{ $day['total'] }}</b>
                </div>
            @endforeach
        </div>

        <div class="chart-summary">
            <div><small>Total Peminjaman</small><strong>{{ $totalBorrowings }}</strong></div>
            <div><small>Aktif</small><strong class="text-blue">{{ $activeBorrowings }}</strong></div>
            <div><small>Menunggu</small><strong class="text-orange">{{ $pendingBorrowings }}</strong></div>
        </div>
    </article>

    <article class="panel locker-panel" id="locker">
        <div class="section-heading">
            <div><h2>Smart Locker Status</h2><p>Status kompartemen loker</p></div>
            <a href="#locker" class="small-link">Manage ↗</a>
        </div>

        <div class="locker-legend">
            <span><i class="available"></i>{{ $lockerStats['available'] }} Available</span>
            <span><i class="occupied"></i>{{ $lockerStats['occupied'] }} Occupied</span>
            <span><i class="reserved"></i>{{ $lockerStats['reserved'] }} Reserved</span>
            <span><i class="maintenance"></i>{{ $lockerStats['maintenance'] }} Maint.</span>
        </div>

        <div class="locker-grid">
            @foreach ($lockers as $locker)
                <div class="locker-slot {{ $locker['status'] }}">
                    <strong>{{ $locker['name'] }}</strong>
                    <small>{{ ['available' => 'Tersedia', 'occupied' => 'Terisi', 'reserved' => 'Dipesan', 'maintenance' => 'Perbaikan'][$locker['status']] }}</small>
                </div>
            @endforeach
        </div>
        <p class="locker-foot"><span class="online-dot"></span> Status perangkat loker ditampilkan sebagai data contoh</p>
    </article>
</section>

<section class="panel requests-panel" id="borrowings">
    <div class="section-heading requests-heading">
        <div><h2>Recent Borrowing Requests</h2><p>Kelola permintaan peminjaman perangkat dari pengguna</p></div>
        <a href="#borrowings" class="outline-button">Lihat Semua</a>
    </div>

    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>REQUEST ID</th><th>USER</th><th>PROJECT</th><th>REQUEST DATE</th><th>EXPIRES</th><th>STATUS</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recentBorrowings as $borrowing)
                    <tr>
                        <td><strong class="request-id">BRW-{{ str_pad($borrowing['id'], 4, '0', STR_PAD_LEFT) }}</strong></td>
                        <td>
                            <span class="table-user">
                                <i>{{ strtoupper(substr($borrowing['user_name'], 0, 1)) }}</i>
                                {{ $borrowing['user_name'] }}
                            </span>
                        </td>
                        <td>{{ $borrowing['project_title'] }}</td>
                        <td>{{ \Carbon\Carbon::parse($borrowing['request_date'])->format('d M Y') }}</td>
                        <td>{{ $borrowing['expires_date'] ? \Carbon\Carbon::parse($borrowing['expires_date'])->format('d M Y') : '-' }}</td>
                        <td><span class="status-pill {{ $borrowing['status'] }}">{{ ucfirst(str_replace('_', ' ', $borrowing['status'])) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state">Belum ada permintaan peminjaman.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<section class="bottom-grid">
    <article class="panel kits-panel" id="kits">
        <div class="section-heading">
            <div><h2>Kits & Components</h2><p>Ringkasan katalog kit IoT</p></div>
            <a href="#kits" class="small-link">+ Tambah Kit</a>
        </div>

        @foreach ($kits as $kit)
            <div class="kit-row">
                <span class="kit-icon">⌘</span>
                <div class="kit-info">
                    <strong>{{ $kit['name'] }}</strong>
                    <small>{{ $kit['detail'] }}</small>
                    <span>{{ $kit['price'] }} <i>•</i> Stok: {{ $kit['stock'] }}</span>
                </div>
                <span class="kit-stock {{ $kit['stock'] <= 4 ? 'low' : '' }}">{{ $kit['status'] }}</span>
            </div>
        @endforeach
        <p class="sample-note">* Data kit masih contoh dan belum terhubung ke database katalog.</p>
    </article>

    <article class="panel activity-panel" id="activity">
        <div class="section-heading">
            <div><h2>Activity Log</h2><p>Aktivitas terbaru sistem</p></div>
            <span class="live-chip">● Sample view</span>
        </div>

        <div class="activity-list">
            <div class="activity-item">
                <i class="activity-dot green"></i>
                <div><strong>Dashboard admin dibuka</strong><small>Ringkasan data ditampilkan</small><time>Baru saja</time></div>
            </div>
            <div class="activity-item">
                <i class="activity-dot blue"></i>
                <div><strong>Data peminjaman contoh</strong><small>{{ $totalBorrowings }} data peminjaman ditampilkan</small><time>{{ now()->format('H:i') }}</time></div>
            </div>
            <div class="activity-item">
                <i class="activity-dot orange"></i>
                <div><strong>Permintaan menunggu persetujuan</strong><small>{{ $pendingBorrowings }} permintaan perlu ditinjau</small><time>{{ now()->format('d M Y') }}</time></div>
            </div>
        </div>
    </article>
</section>
@endsection

@push('scripts')
<script>
    const menuToggle = document.getElementById('menuToggle');
    const adminSidebar = document.getElementById('adminSidebar');

    menuToggle?.addEventListener('click', () => {
        adminSidebar?.classList.toggle('sidebar-open');
    });

    document.addEventListener('click', (event) => {
        if (
            window.innerWidth <= 900 &&
            adminSidebar &&
            menuToggle &&
            !adminSidebar.contains(event.target) &&
            !menuToggle.contains(event.target)
        ) {
            adminSidebar.classList.remove('sidebar-open');
        }
    });
</script>
@endpush
