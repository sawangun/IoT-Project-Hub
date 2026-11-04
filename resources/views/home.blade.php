@extends('layouts.app')

@section('content')
{{-- HERO --}}
<section class="bg-gradient-to-b from-blue-50/60 to-slate-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-10 sm:pt-16 pb-16 sm:pb-20 grid lg:grid-cols-2 gap-12 items-center">
    <div>
      <span class="mono text-[10px] bg-white border border-slate-200 px-3 py-1.5 rounded-full">● EKOSISTEM IOT KAMPUS TERBUKA + SMART LOCKER 24/7</span>
      <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight mt-6 leading-tight">Eksplorasi Proyek IoT, Pinjam Hardware Mandiri & Bangun Portofolio Riset Kampus</h1>
      <p class="text-slate-600 mt-5 max-w-xl">Akses ratusan modul mikrokontroler presisi, sensor terkalibrasi, dan repositori skematik open-hardware. Buka kabinet loker lab otomatis menggunakan QR Code tanpa antrean birokrasi manual.</p>
      <div class="flex flex-wrap gap-3 mt-8">
        <a href="{{ route('projects.index') }}" class="bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg shadow-lg shadow-blue-600/20 hover:bg-blue-800">Mulai Eksplorasi Proyek →</a>
        <a href="{{ route('borrow.index') }}" class="bg-white border border-slate-200 font-semibold px-6 py-3 rounded-lg text-blue-700">▶ Lihat Cara Kerja Smart Locker</a>
      </div>
      <dl class="grid grid-cols-2 sm:grid-cols-4 gap-6 mt-12">
        @foreach ($stats as $s)
          <div><dt class="text-xl font-bold text-blue-700">{{ $s['value'] }}</dt><dd class="text-xs text-slate-500">{{ $s['label'] }}</dd></div>
        @endforeach
      </dl>
    </div>
    <div class="relative mb-8 lg:mb-0">
      <div class="bg-white rounded-3xl border border-slate-200 shadow-xl p-3">
        <img src="https://images.unsplash.com/photo-1558002038-1055907df827?w=900" alt="Smart Locker Node" class="rounded-2xl w-full h-64 sm:h-96 object-cover">
        <div class="mono text-[10px] mt-3 px-2 flex justify-between text-slate-500">
          <span>CLUSTER SOLENOID 100% ONLINE</span><span>PING 12ms</span>
        </div>
      </div>
      <div class="absolute -top-4 right-2 sm:right-0 bg-white border border-slate-200 rounded-xl shadow px-4 py-2 text-xs">
        <b>Akses 60 Detik</b><br><span class="text-slate-500">Scan QR & Ambil Alat</span>
      </div>
      <div class="absolute -bottom-6 left-2 sm:-left-6 bg-white border border-slate-200 rounded-xl shadow px-4 py-3 text-xs">
        <b>Loadcell Presisi</b><br><span class="mono text-slate-500">Toleransi Berat ±0.5g</span><br><span class="text-emerald-600 font-semibold">✓ Auto-Verified</span>
      </div>
    </div>
  </div>
</section>

{{-- TELEMETRI LOKER --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6">
  <div id="telemetry" class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-6 flex flex-wrap items-center gap-6">
    <div class="w-full lg:w-auto lg:min-w-64">
      <h2 class="font-bold">Telemetri Ketersediaan Loker <span class="mono text-[10px] bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded ml-1">LIVE</span></h2>
      <p class="text-xs text-slate-500">Status kompartemen hardware terverifikasi sensor per detik ini</p>
    </div>
    @foreach ($lockers as $i => $l)
      <div class="flex-1 min-w-[10rem] w-full sm:w-auto border border-slate-200 rounded-xl p-4" data-locker="{{ $i }}">
        <div class="text-xs font-semibold flex justify-between">{{ $l['name'] }} <span class="text-emerald-500">●</span></div>
        <div class="mt-2"><span class="text-2xl font-bold" data-ready>{{ $l['ready'] }}</span> / <span data-total>{{ $l['total'] }}</span>
          <span class="mono text-xs text-slate-500">Kompartemen Siap</span></div>
        <div class="h-1.5 bg-slate-100 rounded mt-2"><div data-bar class="h-1.5 bg-blue-600 rounded" style="width: {{ round($l['ready'] / $l['total'] * 100) }}%"></div></div>
      </div>
    @endforeach
    <a href="{{ route('lockers.index') }}" class="text-sm font-semibold border border-slate-200 rounded-lg px-4 py-2">Peta Loker ↗</a>
  </div>
</section>

{{-- KEUNGGULAN --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 mt-24">
  <div class="text-center max-w-2xl mx-auto">
    <p class="mono text-xs text-blue-700 tracking-widest">INTELLIGENT LAB INFRASTRUCTURE</p>
    <h2 class="text-3xl font-extrabold mt-2">Mengapa Memilih IoT Project Hub?</h2>
    <p class="text-slate-600 text-sm mt-3">Kami mereduksi birokrasi perizinan peminjaman alat laboratorium konvensional dari hitungan hari menjadi detik berkat otomasi loker terdesentralisasi dan repositori riset terverifikasi.</p>
  </div>
  <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5 mt-10">
    @foreach ($features as $f)
      <div class="bg-white border border-slate-200 rounded-2xl p-6">
        <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 grid place-items-center mb-5">◈</div>
        <h3 class="font-bold">{{ $f['title'] }}</h3>
        <p class="text-xs text-slate-600 mt-2">{{ $f['desc'] }}</p>
      </div>
    @endforeach
  </div>
</section>

{{-- KATALOG --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 mt-24">
  <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-8">
    <div>
      <p class="mono text-xs text-blue-700">PROJECT MARKETPLACE <span class="bg-blue-50 px-2 py-0.5 rounded ml-1">Tersedia di Kampus</span></p>
      <h2 class="text-3xl font-extrabold mt-2">Katalog Proyek & Modul Unggulan</h2>
    </div>
    <a href="{{ route('projects.index') }}" class="text-sm font-semibold text-blue-700">Lihat Semua 120+ Proyek Terdaftar →</a>
  </div>
  <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
    @foreach ($projects as $project)
      @include('partials.project-card', ['project' => $project])
    @endforeach
  </div>
</section>

{{-- WORKFLOW --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 mt-24">
  <div class="bg-gradient-to-br from-blue-50 to-white border border-slate-200 rounded-3xl p-5 sm:p-10">
    <div class="grid lg:grid-cols-2 gap-6 items-end">
      <div><p class="mono text-xs text-blue-700">WORKFLOW OTOMASI LAB</p>
        <h2 class="text-3xl font-extrabold mt-2">4 Langkah Mudah Meminjam Mandiri</h2></div>
      <p class="text-sm text-slate-600">Sistem tersinkronisasi penuh dengan kartu mahasiswa elektronik dan aplikasi web kami untuk memastikan keamanan aset riset kampus.</p>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5 mt-10">
      @foreach ($steps as $s)
        <div class="bg-white border border-slate-200 rounded-2xl p-6 flex flex-col">
          <span class="mono text-2xl font-bold text-blue-700">{{ $s['no'] }}</span>
          <h3 class="font-bold mt-6">{{ $s['title'] }}</h3>
          <p class="text-xs text-slate-600 mt-2 flex-1">{{ $s['desc'] }}</p>
          <span class="mono text-[10px] text-blue-700 mt-5">● {{ $s['note'] }}</span>
        </div>
      @endforeach
    </div>
  </div>
</section>

{{-- TESTIMONI --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 mt-24">
  <div class="text-center max-w-2xl mx-auto">
    <p class="mono text-xs text-blue-700 tracking-widest">IMPAK MAHASISWA & RISET</p>
    <h2 class="text-3xl font-extrabold mt-2">Kisah Sukses Mahasiswa Periset</h2>
    <p class="text-sm text-slate-600 mt-3">Membantu ratusan mahasiswa menyelesaikan skripsi rekayasa elektronika dan memenangkan kompetisi nasional tepat waktu.</p>
  </div>
  <div class="grid md:grid-cols-3 gap-5 mt-10">
    @foreach ($testimonials as $t)
      <figure class="bg-white border border-slate-200 rounded-2xl p-6 flex flex-col">
        <div class="text-amber-400">★★★★★</div>
        <blockquote class="text-sm text-slate-700 italic mt-4 flex-1">“{{ $t['quote'] }}”</blockquote>
        <figcaption class="flex items-center gap-3 mt-6">
          <span class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 grid place-items-center font-bold">{{ substr($t['name'], 0, 1) }}</span>
          <span class="text-xs"><b class="block text-sm">{{ $t['name'] }}</b><span class="text-slate-500">{{ $t['role'] }}</span></span>
        </figcaption>
      </figure>
    @endforeach
  </div>
</section>

{{-- CTA --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 mt-24">
  <div class="rounded-3xl bg-gradient-to-br from-slate-900 via-blue-950 to-blue-800 text-white p-6 sm:p-12">
    <span class="mono text-[10px] bg-white/10 px-3 py-1.5 rounded-full">🔒 AKSES TERBUKA CIVITAS AKADEMIK</span>
    <h2 class="text-3xl sm:text-4xl font-extrabold mt-6 max-w-xl leading-tight">Punya Ide Riset IoT atau Butuh Hardware untuk Tugas Akhir?</h2>
    <p class="text-blue-100 mt-4 max-w-2xl text-sm">Daftarkan akun mahasiswa Anda sekarang menggunakan Single Sign-On (SSO) Kampus dan nikmati akses instan ke seluruh ekosistem laboratorium smart locker tanpa biaya tambahan.</p>
    <div class="flex flex-wrap gap-3 mt-8">
      <a href="{{ route('register') }}" class="bg-white text-blue-800 font-semibold px-6 py-3 rounded-lg">🎓 Daftar Akun Mahasiswa (Gratis)</a>
      <a href="{{ route('login') }}" class="bg-white/10 font-semibold px-6 py-3 rounded-lg">Masuk ke Akun →</a>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
// Polling telemetri loker tiap 5 detik
async function refreshLockers() {
  try {
    const res = await fetch('{{ route('lockers.status') }}', {headers: {'Accept': 'application/json'}});
    const data = await res.json();
    data.lockers.forEach((l, i) => {
      const el = document.querySelector(`[data-locker="${i}"]`);
      if (!el) return;
      el.querySelector('[data-ready]').textContent = l.ready;
      el.querySelector('[data-total]').textContent = l.total;
      el.querySelector('[data-bar]').style.width = l.percent + '%';
    });
  } catch (e) { /* abaikan */ }
}
setInterval(refreshLockers, 5000);
</script>
@endpush
