<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Jelajahi proyek IoT dan perangkat riset kampus.">
    <title>@yield('title', 'IoT Project Hub')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/guest.css') }}?v=2">
      @if (request()->routeIs('dashboard'))
          <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
      @endif
    <link rel="stylesheet" href="{{ asset('css/dashboard-nav.css') . '?v=3' }}">
    <link rel="stylesheet" href="{{ asset('css/user-pages.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link rel="stylesheet" href="{{ asset('css/signup.css') }}">
    @if (request()->routeIs('projects.*'))
        <link rel="stylesheet" href="{{ asset('css/projects.css') }}">
    @endif
    @if (request()->routeIs('dashboard', 'projects.*', 'catalog.*', 'borrow.*', 'user.history', 'checkout.*', 'about'))
        <link rel="stylesheet" href="{{ asset('css/user-portal.css') }}?v=1">
    @endif
</head>
<body>
    @include('partials.navbar')
    <main>@yield('content')</main>
    @include('partials.footer')
    <!-- <script>
      document.addEventListener('DOMContentLoaded', () => {
          const toggle = document.querySelector('.nav-toggle');
          const menu = document.querySelector('.nav-menu');
          if (!toggle || !menu) return;
          toggle.addEventListener('click', () => {
              const open = menu.classList.toggle('is-open');
              toggle.setAttribute('aria-expanded', String(open));
          });
          menu.querySelectorAll('a').forEach(link => link.addEventListener('click', () => {
              menu.classList.remove('is-open');
              toggle.setAttribute('aria-expanded', 'false');
          }));
      });
    </script> -->

    {{-- Interaksi navbar (menu, dropdown, klik di luar, Escape, dan Ctrl/Cmd+K)
         ditangani satu kali di partials/navbar.blade.php. --}}
</body>
</html>
