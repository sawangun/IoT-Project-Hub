@extends('layouts.app')
@section('title', 'Daftar Akun — IoT Project Hub')

@section('content')
<section class="signup-page">
    <div class="signup-topline">
        <a href="{{ route('home') }}" class="signup-back">← Kembali ke Beranda</a>
        <span class="signup-status"><i></i> REGISTRASI EKOSISTEM PERANGKAT KERAS KAMPUS</span>
        <small>◈ PDDikti SSO Node: Online (v2.8.4)</small>
    </div>

    <div class="signup-layout">
        <aside class="signup-promo">
            <div class="promo-label"><span>NEXUS NODE</span><small>SEC-ID: FT-LAB-2025</small><b>⚙</b></div>
            <h2>Infrastruktur Lab IoT dalam Genggaman</h2>
            <p>Akses cepat instrumen riset, dispensasi modul mikrokontroler real-time, dan kolaborasi repositori open-hardware lintas program studi.</p>
            <div class="promo-photo">
                <img src="https://images.unsplash.com/photo-1581092921461-eab62e97a780?auto=format&fit=crop&w=900&q=85" alt="Mahasiswa bekerja dengan perangkat elektronik">
                <span>◉ Smart Locker Active: 24 Unit</span>
                <small>ESP32 / LoRaWAN</small>
            </div>
            <div class="promo-feature"><b>✣</b><div><strong>Akses Mandiri Smart Locker 24/7</strong><p>Pinjam mikrokontroler & sensor kapan saja dengan scan QR di laboratorium fakultas.</p></div></div>
            <div class="promo-feature"><b>ϟ</b><div><strong>Subsidi & Komponen Terkurasi</strong><p>Diskon harga mahasiswa untuk kit praktikum dan pinout terstandar laboratorium akademik.</p></div></div>
            <div class="promo-feature"><b>⌘</b><div><strong>Portofolio Riset Terindeks</strong><p>Publikasikan prototipe Anda, raih sitasi akademik, dan bagikan skematik terverifikasi OSHWA.</p></div></div>
            <div class="promo-footer">◉ Sistem Terhubung dengan Database Akademik <strong>Fakultas Teknik & PDDikti.</strong></div>
        </aside>

        <div class="signup-card">
            <div class="signup-step"><i></i> LANGKAH 1 DARI 2: REGISTRASI PROFIL</div>
            <h1>Buat Akun Peneliti & Mahasiswa</h1>
            <p class="signup-intro">Daftarkan identitas akademik Anda untuk mendapatkan akses instan ke dispenser Smart Locker, unduh source code open-hardware, dan terbitkan proyek IoT.</p>

            <div class="sso-row"><span>U</span><strong>Daftar Cepat via SSO Universitas</strong><em>Validasi Otomatis</em></div>
            <p class="sso-note">Otomatis memvalidasi status mahasiswa aktif PDDikti dengan akun SSO Kampus.</p>
            <div class="form-divider"><span>ATAU LENGKAPI FORMULIR PENDAFTARAN MANUAL</span></div>

            <form method="POST" action="{{ route('register.store') }}" class="signup-form">
                @csrf
                <div class="signup-fields">
                    <div class="field"><label for="name">Nama Lengkap Sesuai KTM <b>*</b></label><div class="field-control"><input id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Rafi Pratama" required autocomplete="name"><span>▣</span></div>@error('name')<small class="field-error">{{ $message }}</small>@enderror</div>
                    <div class="field"><label for="student_id">NIM / Nomor Induk Mahasiswa</label><div class="field-control"><input id="student_id" name="student_id" type="text" value="{{ old('student_id') }}" placeholder="2110502041"><span>◉</span></div>@error('student_id')<small class="field-error">{{ $message }}</small>@enderror</div>
                    <div class="field"><label for="email">Email Akademik Kampus <b>*</b></label><div class="field-control"><input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="nama@mahasiswa.kampus.ac.id" required autocomplete="email"><span>◎</span></div>@error('email')<small class="field-error">{{ $message }}</small>@enderror</div>
                    <div class="field"><label for="department">Departemen / Program Studi <b>*</b></label><div class="field-control"><select id="department" name="department" required><option value="">Pilih Program Studi Anda</option><option value="Teknik Informatika" @selected(old('department') === 'Teknik Informatika')>Teknik Informatika</option><option value="Sistem Informasi" @selected(old('department') === 'Sistem Informasi')>Sistem Informasi</option><option value="Teknik Elektro" @selected(old('department') === 'Teknik Elektro')>Teknik Elektro</option><option value="Teknik Komputer" @selected(old('department') === 'Teknik Komputer')>Teknik Komputer</option><option value="Lainnya" @selected(old('department') === 'Lainnya')>Lainnya</option></select><span>⌄</span></div>@error('department')<small class="field-error">{{ $message }}</small>@enderror</div>
                    <div class="field"><label for="year">Angkatan / Tahun Masuk <b>*</b></label><div class="field-control"><input id="year" name="year" type="number" min="1990" max="{{ date('Y') + 1 }}" value="{{ old('year') }}" placeholder="{{ date('Y') }}" required><span>▣</span></div>@error('year')<small class="field-error">{{ $message }}</small>@enderror</div>
                    <div class="field"><label for="phone">Nomor WhatsApp Aktif <b>*</b></label><div class="field-control"><input id="phone" name="phone" type="tel" value="{{ old('phone') }}" placeholder="081234567890" required autocomplete="tel"><span>▤</span></div><small class="field-hint">Digunakan untuk kirim OTP locker fisik.</small>@error('phone')<small class="field-error">{{ $message }}</small>@enderror</div>
                    <div class="field"><label for="account_type">Jenis Akun <b>*</b></label><div class="field-control"><select id="account_type" name="account_type" required><option value="mahasiswa" @selected(old('account_type', 'mahasiswa') === 'mahasiswa')>Mahasiswa</option><option value="umum" @selected(old('account_type') === 'umum')>Umum</option></select><span>⌄</span></div>@error('account_type')<small class="field-error">{{ $message }}</small>@enderror</div>
                    <div class="field"><label for="password">Kata Sandi <b>*</b></label><div class="field-control"><input id="password" name="password" type="password" placeholder="Minimal 8 karakter" required autocomplete="new-password"><button class="password-toggle" type="button" data-target="password" aria-label="Tampilkan kata sandi">◎</button></div><small class="field-hint">B+ · alfanumerik, huruf besar & angka belum terisi.</small>@error('password')<small class="field-error">{{ $message }}</small>@enderror</div>
                    <div class="field"><label for="password_confirmation">Konfirmasi Kata Sandi <b>*</b></label><div class="field-control"><input id="password_confirmation" name="password_confirmation" type="password" placeholder="Ulangi kata sandi" required autocomplete="new-password"><button class="password-toggle" type="button" data-target="password_confirmation" aria-label="Tampilkan konfirmasi kata sandi">◎</button></div><small class="field-hint">Pastikan kata sandi cocok.</small></div>
                </div>

                <fieldset class="role-field"><legend>Peran Utama & Minat Ekosistem IoT (Pilih yang relevan):</legend>
                    <label><input type="checkbox" name="interests[]" value="Peminjam Hardware Praktikum" @checked(in_array('Peminjam Hardware Praktikum', old('interests', [])) )><span>▤ &nbsp; Peminjam Hardware Praktikum</span></label>
                    <label><input type="checkbox" name="interests[]" value="Pengembang Riset Skripsi" @checked(in_array('Pengembang Riset Skripsi', old('interests', [])) )><span>▣ &nbsp; Pengembang Riset Skripsi</span></label>
                    <label><input type="checkbox" name="interests[]" value="Dosen / Pembimbing Lab" @checked(in_array('Dosen / Pembimbing Lab', old('interests', [])) )><span>♙ &nbsp; Dosen / Pembimbing Lab</span></label>
                </fieldset>

                <label class="terms-row"><input type="checkbox" name="terms" value="1" required><span>Saya menyetujui <a href="#terms">Ketentuan Peminjaman Hardware Smart Locker</a>, Standar Lisensi Open-Hardware (OSHWA), dan <a href="#privacy">Kebijakan Privasi Kampus</a>.</span></label>
                @error('terms')<small class="field-error">{{ $message }}</small>@enderror

                <button class="signup-submit" type="submit">♙ &nbsp; Daftar & Masuk</button>
            </form>
            <p class="signup-login">Sudah memiliki akun terdaftar? <a href="{{ route('login') }}">Masuk ke Akun Anda →</a></p>
            <div class="signup-card-foot"><span>TLS 1.3 · 256-BIT ENCRYPTION</span><span>HARDWARE DISPENSER API: READY</span></div>
        </div>
    </div>
    <div class="signup-page-footer"><span>© {{ date('Y') }} IoT Project Hub. Platform Kolaborasi & Peminjaman Hardware Mahasiswa.</span><span><a href="#privacy">Privasi</a> &nbsp; <a href="#terms">Ketentuan</a></span></div>
</section>
<script>
document.querySelectorAll('.password-toggle').forEach(button => {
    button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.target);
        input.type = input.type === 'password' ? 'text' : 'password';
    });
});
</script>
@endsection
