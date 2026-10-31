<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Admin dashboard Sawangun IoT Project Hub">
    <title>@yield('title', 'Admin Dashboard | Sawangun IoT Hub')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}?v=1">
    @stack('styles')
</head>
<body class="admin-body">
    <div class="admin-shell">
        @include('partials.admin.sidebar')
        <div class="admin-main">
            <main class="admin-content">
                @include('partials.admin.topbar')
                @yield('content')
            </main>
            @include('partials.admin.footer')
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-admin-profile-menu]').forEach(function (menu) {
                const trigger = menu.querySelector('.admin-profile-trigger');
                trigger?.addEventListener('click', function (event) {
                    event.stopPropagation();
                    const open = menu.classList.toggle('is-open');
                    trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
                });
            });
            document.addEventListener('click', function () {
                document.querySelectorAll('[data-admin-profile-menu].is-open').forEach(function (menu) {
                    menu.classList.remove('is-open');
                    menu.querySelector('.admin-profile-trigger')?.setAttribute('aria-expanded', 'false');
                });
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
