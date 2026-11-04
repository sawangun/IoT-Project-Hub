@extends('layouts.app')

@section('title', 'Lupa Kata Sandi — IoT Project Hub')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endpush

@section('content')
<section class="login-page">
    <div class="login-topline">
        <a class="login-back" href="{{ route('login') }}">← Kembali ke Login</a>
        <span class="login-status"><i></i> PEMULIHAN AKUN</span>
        <span class="login-system">IoT Project Hub</span>
    </div>
    <div class="login-layout">
        <aside class="login-aside">
            <div class="login-aside-meta"><span class="login-chip">ACCOUNT RECOVERY</span></div>
            <h1>Akses akun Anda kembali.</h1>
            <p class="login-aside-copy">Masukkan email yang digunakan saat mendaftar. Jika akun terdaftar, kami akan mengirim tautan untuk mengatur ulang kata sandi. Tautan berlaku selama {{ config('services.supabase.recovery_link_minutes', 60) }} menit.</p>
        </aside>
        <section class="login-panel">
            <div class="login-panel-head">
                <span class="login-eyebrow"><i></i> LUPA KATA SANDI</span>
                <h2>Atur ulang kata sandi</h2>
                <p>Gunakan email akun Anda untuk menerima tautan pemulihan. Tautan kedaluwarsa dalam {{ config('services.supabase.recovery_link_minutes', 60) }} menit.</p>
            </div>
            @if (session('status'))
                <div class="recovery-notice" role="status" aria-live="polite">
                    <div class="recovery-notice-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none"><path d="M4.5 7.5 12 13l7.5-5.5M6 5.5h12A1.5 1.5 0 0 1 19.5 7v10A1.5 1.5 0 0 1 18 18.5H6A1.5 1.5 0 0 1 4.5 17V7A1.5 1.5 0 0 1 6 5.5Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                    <div class="recovery-notice-content">
                        <strong>Permintaan pemulihan diterima</strong>
                        <p>{{ session('status') }}</p>
                        <div class="recovery-notice-tip"><span aria-hidden="true">ⓘ</span> Periksa juga folder Spam atau Promotions. Demi keamanan, tautan hanya dapat digunakan dalam {{ config('services.supabase.recovery_link_minutes', 60) }} menit.</div>
                    </div>
                </div>
            @endif
            @if ($errors->any())
                <div class="login-errors" role="alert">
                    @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            @endif
            <form class="login-form" method="POST" action="{{ route('password.email') }}">
                @csrf
                <label for="email">Email terdaftar <span>*</span></label>
                <div class="login-input-wrap"><span class="login-input-icon">✉</span><input id="email" name="email" type="email" autocomplete="email" placeholder="nama@kampus.ac.id" value="{{ old('email') }}" required maxlength="255"></div>
                <button class="login-submit" type="submit">Kirim Tautan Pemulihan <span>→</span></button>
            </form>
            <a class="login-home-link" href="{{ route('login') }}">← Kembali ke login</a>
        </section>
    </div>
    <p class="login-copyright">© {{ date('Y') }} IoT Project Hub</p>
</section>
@endsection
