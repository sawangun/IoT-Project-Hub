@extends('layouts.admin')
@section('title','Tambah Pengguna Baru | Sawangun IoT Hub')
@push('styles')<link rel="stylesheet" href="{{ asset('css/admin-users.css') }}?v=1">@endpush
@section('content')
<form method="post" action="{{ route('admin.users.store') }}">@csrf
<div class="create-title"><div><small>Pengguna › <b>Registrasi Pengguna Baru</b></small><h1>Registrasi Pengguna & Kredensial Akses<br>Lab</h1><p>Daftarkan mahasiswa peneliti, dosen pembimbing, atau asisten laboratorium baru. Konfigurasikan hak akses fisik Kios Smart Locker, kartu RFID, dan kuota peminjaman hardware IoT.</p></div><div><a class="btn ghost" href="{{ route('admin.users.index') }}">Batal</a><button class="btn primary">♙ Simpan & Daftarkan Pengguna</button></div></div>
<div class="create-grid"><div><section class="panel form-card"><h2>♙ Tipe Akun & Peran Laboratorium <small>Langkah 1/3</small></h2><p>Pilih profil otorisasi pengguna untuk menentukan batas peminjaman dan hak akses solenoid locker secara otomatis.</p><div class="role-select">@foreach([['Mahasiswa Reguler','Akses praktikum kurikuler reguler.'],['Riset TA / MBKM','Akses penuh riset hardware tingkat lanjut.'],['Dosen / Reviewer','Persetujuan usulan & master override.'],['Asisten Lab (Staff)','Operator locker, audit, & kalibrasi.']] as $i=>$r)@php($activeRole = old('role', 'Mahasiswa Reguler'))<label class="role-option {{ $activeRole === $r[0] ? 'selected' : '' }}"><b>{{$r[0]}}</b><span>{{$r[1]}}</span><input type="radio" name="role" value="{{$r[0]}}" {{ $activeRole === $r[0] ? 'checked' : '' }}></label>@endforeach</div></section><section class="panel form-card"><h2>♙ Data Identitas & Akademik <button type="button">↻ Tarik Data dari SIAKAD</button></h2><div class="fields"><label>NIM / NIP Identitas Resmi *<input name="identity" value="2110511048"></label><label>Nama Lengkap (Sesuai KTM / SK) *<input name="name" value="Favian Aryasatya Pratama"></label><label>Email Kampus Resmi (.ac.id) *<input name="email" value="favian.aryasatya@student.ac.id"></label><label>Nomor WhatsApp / HP Aktif *<input name="phone" value="+62 812-9843-1120"></label><label>Program Studi & Fakultas *<input value="S1 Teknik Informatika - FTEIC"></label><label>Semester<input value="Semester 7 (Tujuh)"></label><label>Angkatan<input value="2021"></label><label class="wide">Dosen Pembimbing Riset (Opsional)<select><option>Dr. Ir. Hendra Gunawan, M.T. (Lab Sistem Tertanam & IoT)</option></select></label></div></section></div><div><section class="panel form-card"><h2>◉ Registrasi RFID Kios Locker <span class="ok">● USB Reader Aktif</span></h2><p>Dekatkan kartu KTM fisik / tag RFID 13.56 MHz pada USB desktop reader Sawangun Station.</p><div class="uid"><small>UID Hex Kartu Fisik</small><strong>5A:3F:88:C2</strong><button type="button">Tap Reader</button></div><h3>Konfigurasi Akses Smart Locker</h3>@foreach([['Otorisasi Solenoid 24 Jam','Bypass jam tutup lab via Kios ESP32'],['Batas Kuota Kit Simultan','Jumlah locker kit aktif serentak: 5'],['Status Bebas Kompen (Clean)','Tidak ada denda atau hardware tertinggal']] as $x)<div class="toggle-row"><div><b>{{$x[0]}}</b><span>{{$x[1]}}</span></div><i></i></div>@endforeach</section><section class="panel pass-card"><h3>Pratinjau Kredensial Lab Cerdas <small>Virtual Pass</small></h3><div class="virtual-pass"><b>SAWANGUN</b><small>IOT HARDWARE PASS</small><h3>Favian Aryasatya Pratama</h3><p>NIM: 2110511048<br>S1 Teknik Informatika</p><footer>UID RFID<br><b>5A:3F:88:C2</b>　　HAK AKSES<br><b>24/7 · 5 Kit</b></footer></div><p>ⓘ Kredensial langsung otomatis tersinkronisasi ke 4 unit Master Kios Locker via MQTT Protocol.</p></section></div></div>
<div class="sticky-save"><span>✓ <b>Data Valid & Siap Didaftarkan</b><small>SIAKAD sinkron • Reader online • Token locker siap di-generate</small></span><button type="button" class="btn ghost">Simpan Draf</button><a href="{{route('admin.users.index')}}">Batal</a><button class="btn primary">♙ Simpan & Daftarkan Pengguna</button></div></form>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const roleOptions = document.querySelectorAll('.role-select .role-option');
    roleOptions.forEach(function (option) {
        const radio = option.querySelector('input[type="radio"]');
        option.addEventListener('click', function () {
            roleOptions.forEach(function (item) { item.classList.remove('selected'); });
            option.classList.add('selected');
            radio.checked = true;
            radio.dispatchEvent(new Event('change', { bubbles: true }));
        });
        radio.addEventListener('change', function () {
            if (radio.checked) {
                roleOptions.forEach(function (item) { item.classList.remove('selected'); });
                option.classList.add('selected');
            }
        });
    });
});
</script>
@endpush
@endsection
