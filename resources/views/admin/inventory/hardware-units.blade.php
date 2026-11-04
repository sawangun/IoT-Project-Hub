@extends('layouts.admin')
@section('title','Hardware Units | Sawangun IoT Hub')
@push('styles')<link rel="stylesheet" href="{{ asset('css/admin-inventory.css') }}?v=1">@endpush
@section('content')
<div class="inv-page">
    <div class="inv-breadcrumb">Admin Panel <b>/</b> <strong>Hardware Units</strong></div>
    @if(session('success'))<div class="inv-alert">✓ {{ session('success') }}</div>@endif
    @if($errors->any())<div class="inv-errors">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
    <section class="inv-heading"><div><p class="inv-eyebrow">INVENTORY / PHYSICAL DEVICES</p><h1>Hardware Units</h1><p>Daftarkan unit fisik untuk setiap proyek. Unit berstatus available akan muncul pada halaman pengajuan pengguna.</p></div><span class="inv-chip">Supabase Synced</span></section>
    <section class="inv-stats">
        <article><span>Total unit</span><strong>{{ $units->count() }}</strong><small>Hasil sesuai filter</small></article>
        <article><span>Tersedia</span><strong>{{ $units->where('status','available')->count() }}</strong><small class="green">Bisa diajukan</small></article>
        <article><span>Dipinjam</span><strong>{{ $units->where('status','borrowed')->count() }}</strong><small>Sedang digunakan</small></article>
        <article><span>Maintenance</span><strong>{{ $units->where('status','maintenance')->count() }}</strong><small class="red">Tidak dapat diajukan</small></article>
    </section>
    <section class="inv-panel"><div class="inv-panel-head"><div><h2>Tambah unit hardware</h2><p>Setiap nomor seri harus unik.</p></div></div>
        <form method="POST" action="{{ route('admin.inventory.hardware-units.store') }}" class="inv-form-layout">@csrf
            <div class="inv-form-grid">
                <label class="inv-field full">Proyek<select name="project_id" required><option value="">Pilih proyek</option>@foreach($projects as $project)<option value="{{ $project->id }}" @selected((string)old('project_id')===(string)$project->id)>{{ $project->title }}{{ $project->status==='published'?'':' (Belum dipublikasikan)' }}</option>@endforeach</select></label>
                <label class="inv-field">Kode / serial unit<input name="serial_code" value="{{ old('serial_code') }}" required placeholder="ESP32-SMART-AGRI-001"></label>
                <label class="inv-field">Kondisi<select name="condition" required><option value="baik">Baik</option><option value="perlu_perbaikan">Perlu perbaikan</option><option value="rusak">Rusak</option></select></label>
                <label class="inv-field">Status awal<select name="status" required><option value="available">Available / Tersedia</option><option value="maintenance">Maintenance</option><option value="borrowed">Borrowed / Dipinjam</option></select></label>
            </div>
            <div class="inv-form-actions"><button class="inv-btn primary" type="submit">＋ Simpan Unit Hardware</button></div>
        </form>
    </section>
    <section class="inv-panel"><div class="inv-panel-head"><div><h2>Daftar unit terdaftar</h2><p>Unit hanya muncul ke pengguna jika proyek published dan status unit available.</p></div></div>
        <form class="inv-filters" method="GET"><input name="q" value="{{ $q }}" placeholder="Cari serial atau proyek..."><select name="project_id"><option value="">Semua proyek</option>@foreach($projects as $project)<option value="{{ $project->id }}" @selected((string)$projectId===(string)$project->id)>{{ $project->title }}</option>@endforeach</select><select name="status"><option value="">Semua status</option>@foreach(['available'=>'Available','borrowed'=>'Borrowed','maintenance'=>'Maintenance'] as $key=>$label)<option value="{{ $key }}" @selected($status===$key)>{{ $label }}</option>@endforeach</select><button class="inv-btn secondary">Filter</button></form>
        <div class="inv-table-wrap"><table class="inv-table"><thead><tr><th>Serial unit</th><th>Proyek</th><th>Kondisi</th><th>Status</th><th>Perbarui</th></tr></thead><tbody>
        @forelse($units as $unit)<tr><td><strong>{{ $unit->serial_code }}</strong></td><td>{{ $unit->project_title }}</td><td>{{ str_replace('_',' ',ucfirst($unit->condition)) }}</td><td><span class="inv-status {{ $unit->status==='available'?'available':($unit->status==='borrowed'?'occupied':'maintenance') }}">{{ ucfirst($unit->status) }}</span></td><td><form method="POST" action="{{ route('admin.inventory.hardware-units.update',$unit->id) }}" style="display:flex;gap:6px;align-items:center">@csrf @method('PATCH')<select name="condition"><option value="baik" @selected($unit->condition==='baik')>Baik</option><option value="perlu_perbaikan" @selected($unit->condition==='perlu_perbaikan')>Perlu perbaikan</option><option value="rusak" @selected($unit->condition==='rusak')>Rusak</option></select><select name="status"><option value="available" @selected($unit->status==='available')>Available</option><option value="borrowed" @selected($unit->status==='borrowed')>Borrowed</option><option value="maintenance" @selected($unit->status==='maintenance')>Maintenance</option></select><button class="inv-btn secondary" type="submit">Simpan</button></form></td></tr>
        @empty<tr><td colspan="5" class="inv-empty">Belum ada unit hardware. Tambahkan unit melalui formulir di atas.</td></tr>@endforelse
        </tbody></table></div>
    </section>
</div>
@endsection
