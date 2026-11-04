@extends('layouts.admin')

@section('title', ($mode === 'edit' ? 'Edit Proyek' : 'Tambah Proyek Baru').' | Sawangun IoT Hub')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-projects.css') }}?v=2">
@endpush

@section('content')
<div class="pm-page pm-form-page">
    <a class="pm-back-link" href="{{ route('admin.projects.index') }}">← Kembali ke daftar proyek</a>
    <section class="pm-heading pm-form-heading"><div><p class="pm-eyebrow">{{ $mode === 'edit' ? 'PERBARUI INFORMASI' : 'RUANG KERJA ADMIN' }}</p><h1>{{ $mode === 'edit' ? 'Edit detail proyek' : 'Registrasi & Publikasi Proyek IoT Lab' }}</h1><p>Isi informasi seperlunya. Detail ini akan membantu anggota lab memahami proyek sebelum menghubungi tim pengelola.</p></div><span class="pm-step-chip">{{ $mode === 'edit' ? 'EDIT PROYEK' : 'FORM PROYEK BARU' }}</span></section>
    @if($errors->any())<div class="pm-alert error">Ada beberapa bagian yang perlu dicek lagi:<ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <form method="POST" enctype="multipart/form-data" action="{{ $mode === 'edit' ? route('admin.projects.update', $project['id']) : route('admin.projects.store') }}" class="pm-create-form">
        @csrf @if($mode === 'edit') @method('PUT') @endif
        <div class="pm-form-main">
            <section class="pm-form-card"><div class="pm-form-section-title"><span>01</span><div><h2>Informasi proyek</h2><p>Nama, bidang, dan gambaran singkat proyek.</p></div></div>
                <label class="pm-field full"><span>Nama proyek <b>*</b></span><input name="title" value="{{ old('title', $project['title'] ?? '') }}" placeholder="Contoh: Smart Hydroponic & Water Quality Monitor" maxlength="120" required><small>Gunakan nama yang jelas dan mudah dikenali.</small></label>
                <div class="pm-form-row"><label class="pm-field"><span>Kategori IoT <b>*</b></span><select name="category" required>@foreach(['Smart Agriculture','Smart City & Mobility','Weather & Environment','Healthcare & Biometrics','Energy & Industri','Robotics & Drone','IoT Umum'] as $option)<option value="{{ $option }}" @selected(old('category', $project['category'] ?? '') === $option)>{{ $option }}</option>@endforeach</select></label><label class="pm-field"><span>Tim / laboratorium <b>*</b></span><input name="team" value="{{ old('team', $project['team'] ?? '') }}" placeholder="Nama tim atau lab" required></label></div>
                <div class="pm-form-row three"><label class="pm-field"><span>Tahun proyek <b>*</b></span><input type="number" name="year" min="2000" max="2100" value="{{ old('year', $project['year'] ?? date('Y')) }}" required></label><label class="pm-field"><span>Tingkat kesulitan</span><select name="level">@foreach(['Pemula','Menengah','Lanjutan','Expert'] as $option)<option value="{{ $option }}" @selected(old('level', $project['level'] ?? 'Menengah') === $option)>{{ $option }}</option>@endforeach</select></label><label class="pm-field"><span>Lokasi / loker</span><input name="locker" value="{{ old('locker', $project['locker'] ?? '') }}" placeholder="Mis. Loker A-03"></label></div>
                <label class="pm-field full"><span>Deskripsi proyek <b>*</b></span><textarea name="description" rows="4" maxlength="700" placeholder="Ceritakan fungsi utama proyek dan masalah yang ingin diselesaikan..." required>{{ old('description', $project['description'] ?? '') }}</textarea><small>Maksimal 700 karakter. Tulis dengan bahasa yang mudah dipahami.</small></label>
                <label class="pm-field full"><span>Hardware & komponen <b>*</b></span><input name="hardware" value="{{ old('hardware', $project['hardware'] ?? '') }}" placeholder="ESP32, Sensor DHT22, Relay 4-Channel" required><small>Pisahkan setiap komponen dengan koma.</small></label>
            </section>
            <section class="pm-form-card"><div class="pm-form-section-title"><span>02</span><div><h2>Dokumentasi teknis</h2><p>Tautkan kode sumber dan jelaskan sambungan kabel agar proyek mudah dirakit ulang.</p></div></div>
                <label class="pm-field full"><span>Link kode sumber / GitHub</span><input type="url" name="repo_url" value="{{ old('repo_url', $project['repo_url'] ?? '') }}" placeholder="https://github.com/username/nama-repo"><small>Opsional. Masukkan tautan repositori kode proyek.</small></label>
                <label class="pm-field full"><span>Link diagram / skematik</span><input type="url" name="schematic_url" value="{{ old('schematic_url', $project['schematic_url'] ?? '') }}" placeholder="https://..."><small>Opsional. Bisa berupa diagram rangkaian atau dokumentasi skematik.</small></label>
                <label class="pm-field full"><span>Panduan sambungan kabel</span><textarea name="wiring_guide" rows="5" maxlength="5000" placeholder="Contoh:
DHT22 VCC → ESP32 3V3
DHT22 GND → ESP32 GND
DHT22 DATA → ESP32 GPIO 4">{{ old('wiring_guide', $project['wiring_guide'] ?? '') }}</textarea><small>Tulis satu koneksi per baris. Gunakan format nama komponen/pin → nama komponen/pin.</small></label>
            </section>
            <section class="pm-form-card"><div class="pm-form-section-title"><span>03</span><div><h2>Profil pembuat</h2><p>Informasi pembuat yang akan ditampilkan di halaman detail proyek.</p></div></div>
                <label class="pm-field full"><span>Nama pembuat / tim</span><input name="maker_name" value="{{ old('maker_name', $project['maker_name'] ?? '') }}" maxlength="120" placeholder="Nama pembuat atau nama tim"></label>
                <div class="pm-form-row"><label class="pm-field"><span>Instagram</span><input type="url" name="maker_instagram_url" value="{{ old('maker_instagram_url', $project['maker_instagram_url'] ?? '') }}" placeholder="https://instagram.com/username"></label><label class="pm-field"><span>GitHub</span><input type="url" name="maker_github_url" value="{{ old('maker_github_url', $project['maker_github_url'] ?? '') }}" placeholder="https://github.com/username"></label></div>
                <label class="pm-field full"><span>LinkedIn</span><input type="url" name="maker_linkedin_url" value="{{ old('maker_linkedin_url', $project['maker_linkedin_url'] ?? '') }}" placeholder="https://linkedin.com/in/username"></label>
            </section>
            <section class="pm-form-card"><div class="pm-form-section-title"><span>04</span><div><h2>Publikasi</h2><p>Tentukan apakah proyek sudah siap ditampilkan.</p></div></div>
                <div class="pm-publish-options"><label class="pm-publish-option"><input type="radio" name="status" value="draft" @checked(old('status', $project['status'] ?? 'draft') === 'draft')><span><b>Simpan sebagai draft</b><small>Belum ditampilkan sebagai proyek terbit.</small></span></label><label class="pm-publish-option"><input type="radio" name="status" value="published" @checked(old('status', $project['status'] ?? '') === 'published')><span><b>Publikasikan proyek</b><small>Tandai proyek sebagai siap ditampilkan di katalog.</small></span></label></div>
            </section>
        </div>
        <aside class="pm-form-aside"><section class="pm-form-card pm-preview-card"><div class="pm-form-section-title"><span>↗</span><div><h2>Pratinjau kartu</h2><p>Gambaran sederhana tampilan proyek.</p></div></div><div class="pm-preview-art"><span>⌘</span><small>SAWANGUN / IOT</small></div><div class="pm-preview-copy"><span class="pm-preview-category">{{ old('category', $project['category'] ?? 'Kategori proyek') }}</span><h3>{{ old('title', $project['title'] ?? 'Nama proyek akan tampil di sini') }}</h3><p>{{ old('description', $project['description'] ?? 'Deskripsi singkat membantu pengunjung memahami proyek ini.') }}</p></div><label class="pm-field full"><span>Upload gambar proyek (opsional)</span><input type="file" name="image_file" id="project-image-file" accept="image/jpeg,image/png,image/webp"><small>Format JPG, PNG, atau WEBP. Maksimal 5 MB. Gambar akan tersimpan di server project, tidak perlu link eksternal.</small></label>
            <label class="pm-field full"><span>Atau gunakan URL gambar</span><input type="url" name="image_url" value="{{ old('image_url', isset($project['image']) && filter_var($project['image'], FILTER_VALIDATE_URL) ? $project['image'] : '') }}" placeholder="https://..."><small>Gunakan URL langsung ke file gambar. Jika upload gambar dan mengisi URL sekaligus, file yang di-upload akan dipakai.</small></label>
            @if(!empty($project['image']))<div class="pm-current-image"><span>Gambar saat ini</span><img src="{{ \Illuminate\Support\Str::startsWith($project['image'], ['http://', 'https://']) ? $project['image'] : asset('storage/'.ltrim($project['image'], '/')) }}" alt="Gambar proyek saat ini" id="current-project-image"><small>Upload gambar baru untuk menggantinya.</small></div>@endif
            <img id="project-image-preview" class="pm-upload-preview" alt="Pratinjau gambar yang dipilih" hidden></section><div class="pm-form-note"><span>ⓘ</span><p><b>Terhubung ke database Supabase</b><br>Data proyek dibaca dan disimpan langsung melalui koneksi PostgreSQL Laravel, bukan melalui session.</p></div></aside>
        <div class="pm-form-actions"><a class="pm-cancel" href="{{ route('admin.projects.index') }}">Batalkan</a><button type="submit" name="save_action" value="draft" class="pm-save-draft">Simpan sebagai draft</button><button type="submit" name="save_action" value="publish" class="pm-submit">{{ $mode === 'edit' ? 'Simpan Perubahan' : 'Simpan & Publikasikan' }} <span>↗</span></button></div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('project-image-file');
    const preview = document.getElementById('project-image-preview');
    if (!input || !preview) return;
    input.addEventListener('change', function () {
        const file = input.files && input.files[0];
        if (!file) { preview.hidden = true; preview.removeAttribute('src'); return; }
        preview.src = URL.createObjectURL(file);
        preview.hidden = false;
    });
});
</script>
@endpush
