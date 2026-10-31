{{-- Gunakan kondisi ini pada navbar pengguna agar dashboard dan halaman proyek sama-sama memakai navbar pengguna. --}}
@if (request()->routeIs('dashboard', 'projects.*'))
    {{-- navbar pengguna --}}
@endif

{{-- Contoh link Beranda yang aktif hanya di dashboard --}}
<a href="{{ route('dashboard') }}"
   class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">Beranda</a>

{{-- Contoh link Eksplorasi Proyek yang aktif hanya di halaman proyek --}}
<a href="{{ route('projects.index') }}"
   class="nav-link {{ request()->routeIs('projects.*') ? 'active' : '' }}">Eksplorasi Proyek</a>
