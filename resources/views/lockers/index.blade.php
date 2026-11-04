@extends('layouts.app')
@section('title', 'Peta Loker')
@section('content')
<section class="max-w-5xl mx-auto px-4 sm:px-6 py-14">
  <h1 class="text-3xl font-extrabold">Peta & Status Smart Locker</h1>
  <div class="grid md:grid-cols-3 gap-5 mt-8">
    @foreach ($lockers as $l)
      <div class="bg-white border border-slate-200 rounded-2xl p-6">
        <b>{{ $l['name'] }}</b>
        <p class="mt-3 text-3xl font-bold">{{ $l['ready'] }}<span class="text-base text-slate-500"> / {{ $l['total'] }} siap</span></p>
      </div>
    @endforeach
  </div>
</section>
@endsection
