@extends('layouts.app')

@section('title', 'Buat Kata Sandi Baru — IoT Project Hub')

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
    <div class="auth-single-layout">
        <section class="login-panel auth-single-panel">
            <div class="login-panel-head">
                <span class="login-eyebrow"><i></i> KATA SANDI BARU</span>
                <h2>Perbarui kata sandi</h2>
                <p>Tautan pemulihan hanya dapat digunakan dengan token valid dari email dan kedaluwarsa dalam {{ config('services.supabase.recovery_link_minutes', 60) }} menit. Jika sudah kedaluwarsa, minta tautan baru.</p>
            </div>
            <div id="reset-message" class="login-errors" role="alert" hidden></div>
            <div id="reset-success" class="login-success" role="status" hidden></div>
            <form id="reset-form" class="login-form" hidden>
                <label for="password">Kata sandi baru <span>*</span></label>
                <div class="login-input-wrap"><span class="login-input-icon">⌑</span><input id="password" type="password" autocomplete="new-password" minlength="8" required><button class="login-show-password" type="button" data-target="password" aria-label="Tampilkan kata sandi">◎</button></div>
                <p id="new-password-hint" class="field-hint" aria-live="polite">Gunakan minimal 8 karakter.</p>
                <label for="password-confirmation">Konfirmasi kata sandi <span>*</span></label>
                <div class="login-input-wrap"><span class="login-input-icon">⌑</span><input id="password-confirmation" type="password" autocomplete="new-password" minlength="8" required><button class="login-show-password" type="button" data-target="password-confirmation" aria-label="Tampilkan konfirmasi kata sandi">◎</button></div>
                <p id="confirmation-hint" class="field-hint" aria-live="polite">Masukkan ulang kata sandi yang sama.</p>
                <button id="reset-submit" class="login-submit" type="submit">Simpan Kata Sandi Baru <span>→</span></button>
            </form>
            <a id="reset-login-link" class="login-home-link" href="{{ route('login') }}">← Kembali ke login</a>
        </section>
    </div>
    <p class="login-copyright">© {{ date('Y') }} IoT Project Hub</p>
</section>

<script>
(() => {
    const supabaseUrl = @json(rtrim((string) config('services.supabase.url'), '/'));
    const supabaseKey = @json((string) config('services.supabase.key'));
    const message = document.getElementById('reset-message');
    const success = document.getElementById('reset-success');
    const form = document.getElementById('reset-form');
    const submit = document.getElementById('reset-submit');
    const passwordInput = document.getElementById('password');
    const confirmationInput = document.getElementById('password-confirmation');
    const passwordHint = document.getElementById('new-password-hint');
    const confirmationHint = document.getElementById('confirmation-hint');
    let accessToken = null;

    function validatePasswords() {
        const password = passwordInput.value;
        const confirmation = confirmationInput.value;
        const longEnough = password.length >= 8;
        passwordInput.setCustomValidity(password && !longEnough ? 'Kata sandi minimal 8 karakter.' : '');
        passwordHint.textContent = password && !longEnough
            ? 'Kata sandi masih kurang dari 8 karakter.'
            : (longEnough ? '✓ Panjang kata sandi sudah cukup.' : 'Gunakan minimal 8 karakter.');
        passwordHint.classList.toggle('field-error', Boolean(password && !longEnough));
        passwordHint.classList.toggle('field-valid', Boolean(longEnough));
        const matches = confirmation.length > 0 && password === confirmation;
        confirmationInput.setCustomValidity(confirmation && !matches ? 'Konfirmasi kata sandi tidak cocok.' : '');
        confirmationHint.textContent = !confirmation
            ? 'Masukkan ulang kata sandi yang sama.'
            : (matches ? '✓ Kata sandi cocok.' : 'Kata sandi belum cocok.');
        confirmationHint.classList.toggle('field-error', Boolean(confirmation && !matches));
        confirmationHint.classList.toggle('field-valid', Boolean(matches));
    }
    passwordInput.addEventListener('input', validatePasswords);
    confirmationInput.addEventListener('input', validatePasswords);

    function showError(text) {
        message.textContent = text;
        message.hidden = false;
        success.hidden = true;
    }

    function showSuccess(text) {
        success.textContent = text;
        success.hidden = false;
        message.hidden = true;
    }

    // Supabase places recovery credentials in the URL fragment. Fragments are
    // not sent to Laravel in HTTP requests, so the bearer token stays in browser.
    const fragment = new URLSearchParams(window.location.hash.slice(1));
    const query = new URLSearchParams(window.location.search);
    const urlError = fragment.get('error_description') || query.get('error_description');
    accessToken = fragment.get('access_token');

    if (urlError) {
        showError('Tautan pemulihan tidak valid atau sudah kedaluwarsa. Silakan minta tautan baru.');
    } else if (!accessToken || fragment.get('type') !== 'recovery') {
        showError('Token pemulihan tidak ditemukan. Buka tautan terbaru yang dikirim ke email Anda atau minta tautan baru.');
    } else if (!supabaseUrl || !supabaseKey) {
        showError('Layanan pemulihan belum dikonfigurasi. Silakan hubungi administrator.');
    } else {
        // Remove the bearer token from the visible address bar after reading it.
        history.replaceState(null, document.title, window.location.pathname);
        form.hidden = false;
    }

    document.querySelectorAll('.login-show-password').forEach(button => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.target);
            input.type = input.type === 'password' ? 'text' : 'password';
        });
    });

    form.addEventListener('submit', async event => {
        event.preventDefault();
        const password = passwordInput.value;
        const confirmation = confirmationInput.value;
        validatePasswords();

        if (password.length < 8) {
            showError('Kata sandi harus terdiri dari minimal 8 karakter.');
            return;
        }
        if (password !== confirmation) {
            showError('Konfirmasi kata sandi tidak cocok.');
            return;
        }

        submit.disabled = true;
        submit.textContent = 'Menyimpan...';
        try {
            const response = await fetch(supabaseUrl + '/auth/v1/user', {
                method: 'PUT',
                headers: {
                    'apikey': supabaseKey,
                    'Authorization': 'Bearer ' + accessToken,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ password })
            });

            if (!response.ok) {
                showError('Kata sandi tidak dapat diperbarui. Tautan mungkin sudah kedaluwarsa. Silakan minta tautan baru.');
                return;
            }

            accessToken = null;
            form.reset();
            form.hidden = true;
            showSuccess('Kata sandi berhasil diperbarui. Silakan masuk menggunakan kata sandi baru.');
        } catch (_) {
            showError('Layanan pemulihan tidak dapat dihubungi. Periksa koneksi internet lalu coba lagi.');
        } finally {
            submit.disabled = false;
            submit.innerHTML = 'Simpan Kata Sandi Baru <span>→</span>';
        }
    });
})();
</script>
@endsection
