@extends('layouts.app')
@section('title', 'QR Pass')
@section('content')
<section class="max-w-md mx-auto px-4 sm:px-6 py-14 text-center">
  <h1 class="text-2xl font-extrabold">QR Pass Dinamis</h1>
  <p class="text-sm text-slate-600 mt-2">{{ $project['title'] ?? $borrowing->project_slug }}</p>
  <div class="bg-white border border-slate-200 rounded-2xl p-6 mt-6">
    {{-- Render QR dari token, mis. dengan simplesoftwareio/simple-qrcode: {!! QrCode::size(220)->generate($borrowing->token) !!} --}}
    <div class="mono text-xs break-all bg-slate-50 p-4 rounded">{{ $borrowing->token }}</div>
    <p class="text-xs mt-4 {{ $borrowing->isExpired() ? 'text-red-600' : 'text-slate-500' }}">
      {{ $borrowing->isExpired() ? 'Token kedaluwarsa' : 'Berlaku sampai '.$borrowing->expires_at->format('H:i') }}
    </p>
  </div>
</section>
@endsection
