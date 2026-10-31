{{-- Letakkan di dalam <head> layouts/app.blade.php --}}
@if (request()->routeIs('projects.*'))
    <link rel="stylesheet" href="{{ asset('css/projects.css') }}">
@endif
