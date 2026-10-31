@extends('layouts.admin')
@section('title', 'Formulir Peminjaman Baru | Sawangun IoT Hub')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-borrowings.css') }}?v=1">
@endpush
@section('content')
<div class="br-page br-form-page">
    <a class="br-back" href="{{ route('admin.borrowings.index') }}">← Kembali ke daftar peminjaman</a>
    <section class="br-heading br-form-heading"><div><p class="br-eyebrow">ADMIN / REGISTRASI PEMINJAMAN</p><h1>Formulir Peminjaman Hardware</h1><p>Catat kebutuhan peminjam dan alokasi kit sebelum proses pengambilan di Smart Locker.</p></div><span class="br-form-chip"><i></i> Form admin</span></section>
    @if($errors->any())<div class="br-alert br-error">Ada beberapa bagian yang perlu diperiksa:<ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form method="POST" action="{{ route('admin.borrowings.store') }}" class="br-create-layout">
        @csrf
        <div class="br-form-main">
            <section class="br-form-card"><div class="br-form-section"><span>01</span><div><h2>Identitas peminjam</h2><p>Masukkan data mahasiswa yang akan menggunakan hardware.</p></div><b class="br-verified">◉ Input admin</b></div>
                <label class="br-field"><span>Nomor induk mahasiswa (NIM) <b>*</b></span><input name="nim" value="{{ old('nim') }}" placeholder="Contoh: 2201010088" required maxlength="30"></label>
                <div class="br-field-row"><label class="br-field"><span>Nama lengkap <b>*</b></span><input name="name" value="{{ old('name') }}" placeholder="Nama sesuai kartu mahasiswa" required maxlength="100"></label><label class="br-field"><span>Program studi <b>*</b></span><input name="program" value="{{ old('program') }}" placeholder="S1 Teknik Informatika" required maxlength="100"></label></div>
            </section>
            <section class="br-form-card"><div class="br-form-section"><span>02</span><div><h2>Detail proyek & durasi</h2><p>Tentukan proyek, komponen, dan waktu penggunaan.</p></div></div>
                <label class="br-field"><span>Proyek / kebutuhan riset <b>*</b></span><select name="project" required>@foreach(['Smart Hydroponic Greenhouse','LoRaWAN Micro Weather Station','Edge AI Smart Gate','Wearable SpO2 Monitor','Smart Locker Sensor Network','Proyek IoT lainnya'] as $item)<option value="{{ $item }}" @selected(old('project')===$item)>{{ $item }}</option>@endforeach</select></label>
                <label class="br-field"><span>Hardware & komponen yang dipinjam <b>*</b></span><input name="items" value="{{ old('items') }}" placeholder="ESP32 DevKit, Sensor DHT22, Relay..." required maxlength="240"><small>Pisahkan beberapa item dengan koma.</small></label>
                <div class="br-field-row"><label class="br-field"><span>Tanggal mulai <b>*</b></span><input type="date" name="start" value="{{ old('start', now()->format('Y-m-d')) }}" required></label><label class="br-field"><span>Durasi peminjaman <b>*</b></span><select name="duration" required>@foreach([3,7,14,30] as $days)<option value="{{ $days }}" @selected((string)old('duration','7')===(string)$days)>{{ $days }} hari</option>@endforeach</select></label></div>
                <label class="br-field"><span>Catatan tambahan</span><textarea name="notes" rows="3" placeholder="Kebutuhan khusus, catatan kondisi kit, atau keperluan praktikum...">{{ old('notes') }}</textarea></label>
            </section>
            <section class="br-form-card"><div class="br-form-section"><span>03</span><div><h2>Alokasi Smart Locker</h2><p>Pilih lokasi penyimpanan untuk pengambilan hardware.</p></div></div>
                <div class="br-locker-options">
                    @foreach(['BAY A-03','BAY A-04','BAY A-05','BAY B-02','BAY B-04','BAY C-01'] as $bay)
                    <label class="br-locker-option"><input type="radio" name="locker" value="{{ $bay }}" @checked(old('locker','BAY A-03')===$bay) required><span><b>{{ $bay }}</b><small>{{ in_array($bay,['BAY A-03','BAY A-04','BAY B-02']) ? 'Tersedia' : 'Perlu verifikasi' }}</small></span><i>⌖</i></label>
                    @endforeach
                </div>
                <label class="br-field br-status-field"><span>Status awal <b>*</b></span><select name="status" required><option value="pending" @selected(old('status','pending')==='pending')>Menunggu persetujuan</option><option value="waiting" @selected(old('status')==='waiting')>Disetujui · menunggu pengambilan</option><option value="active" @selected(old('status')==='active')>Sedang dipinjam</option></select><small>Admin bisa memperbarui status setelah formulir disimpan.</small></label>
            </section>
        </div>
        <aside class="br-form-aside">
            <section class="br-preview-card"><div class="br-preview-head"><span>▣</span><div><h2>Ringkasan peminjaman</h2><p>Periksa kembali sebelum disimpan.</p></div></div>
                <div class="br-summary-id"><small>KODE PEMINJAMAN</small><b>BRW-{{ now()->format('Y') }}-AUTO</b><span>Dibuat otomatis saat disimpan</span></div>
                <div class="br-summary-row"><span>Nama peminjam</span><b id="preview-name">Belum diisi</b></div><div class="br-summary-row"><span>Proyek</span><b id="preview-project">{{ old('project','Smart Hydroponic Greenhouse') }}</b></div><div class="br-summary-row"><span>Durasi</span><b id="preview-duration">{{ old('duration','7') }} hari</b></div><div class="br-summary-row"><span>Lokasi</span><b id="preview-locker">{{ old('locker','BAY A-03') }}</b></div>
                <div class="br-summary-note"><span>ⓘ</span><p><b>Data sementara</b><br>Form ini belum terhubung ke database. Peminjaman disimpan di session untuk kebutuhan demo admin.</p></div>
            </section>
            <div class="br-side-tip"><span>✓</span><div><b>Sebelum menyimpan</b><p>Pastikan NIM dan nama sudah benar, komponen sesuai kebutuhan, serta lokasi loker sudah dipilih.</p></div></div>
        </aside>
        <div class="br-form-actions"><a class="br-secondary" href="{{ route('admin.borrowings.index') }}">Batalkan</a><button type="submit" class="br-primary">＋ Buat peminjaman</button></div>
    </form>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const name = document.querySelector('[name="name"]');
    const project = document.querySelector('[name="project"]');
    const duration = document.querySelector('[name="duration"]');
    const locker = document.querySelectorAll('[name="locker"]');
    const update = () => {
        document.getElementById('preview-name').textContent = name.value.trim() || 'Belum diisi';
        document.getElementById('preview-project').textContent = project.value;
        document.getElementById('preview-duration').textContent = duration.value + ' hari';
        document.getElementById('preview-locker').textContent = document.querySelector('[name="locker"]:checked')?.value || 'Belum dipilih';
    };
    [name, project, duration, ...locker].forEach(el => el.addEventListener('input', update));
    update();
});
</script>
@endpush
