@extends('layouts.app')
@section('title', 'Form Pengembalian & Inspeksi Hardware | IoT Project Hub')
@section('content')
<style>
.return-page{background:#f5f7fc;padding:28px 0 56px;min-height:70vh}.return-wrap{max-width:1180px;margin:auto;padding:0 22px}.return-breadcrumb{font-size:13px;color:#69748b;margin-bottom:18px}.return-breadcrumb a{color:#1555d8;text-decoration:none}.return-head{background:#fff;border:1px solid #e8edf6;border-radius:18px;padding:26px 30px;margin-bottom:18px}.return-head h1{font-size:clamp(25px,3vw,34px);margin:8px 0;color:#17223a}.return-head p{color:#65718a;margin:0;line-height:1.6}.return-kicker{font-size:11px;font-weight:800;letter-spacing:.11em;color:#1555d8}.return-meta{display:flex;flex-wrap:wrap;gap:10px;margin-top:18px}.return-meta span{background:#f1f5ff;color:#34415e;padding:8px 12px;border-radius:9px;font-size:12px}.return-alert{padding:13px 16px;border-radius:12px;margin:12px 0;background:#fff4e5;color:#8b5410}.return-grid{display:grid;grid-template-columns:minmax(260px,.82fr) minmax(0,1.5fr);gap:18px;align-items:start}.return-card{background:#fff;border:1px solid #e8edf6;border-radius:18px;padding:24px;box-shadow:0 5px 20px rgba(25,45,90,.035)}.return-card h2{font-size:19px;margin:0 0 7px;color:#17223a}.return-sub{font-size:13px;color:#758097;line-height:1.55;margin:0 0 20px}.return-steps{display:grid;gap:16px}.return-step{display:grid;grid-template-columns:30px 1fr;gap:11px}.return-step b{width:29px;height:29px;border-radius:50%;background:#1254d8;color:white;display:grid;place-items:center;font-size:12px}.return-step strong{display:block;font-size:13px;color:#26334e;margin:2px 0 4px}.return-step p{font-size:12px;color:#758097;margin:0;line-height:1.5}.return-warning{margin-top:20px;background:#fff0ef;color:#a43a36;border-radius:11px;padding:13px;font-size:12px;line-height:1.55}.camera-frame{position:relative;background:#101827;border-radius:14px;overflow:hidden;min-height:250px;aspect-ratio:16/9;display:grid;place-items:center}.camera-frame video,.camera-frame canvas{width:100%;height:100%;object-fit:cover;position:absolute;inset:0}.camera-placeholder{color:#d6deed;text-align:center;padding:25px;z-index:1}.camera-placeholder .cam-icon{font-size:36px;display:block;margin-bottom:9px}.camera-stamp{position:absolute;left:12px;bottom:12px;z-index:2;background:rgba(4,14,25,.78);color:#fff;padding:8px 10px;border-radius:7px;font:11px ui-monospace,monospace;display:none}.camera-controls{display:flex;flex-wrap:wrap;gap:9px;margin-top:13px}.camera-btn{border:0;border-radius:9px;padding:11px 15px;font-weight:700;font-size:13px;cursor:pointer;background:#1254d8;color:#fff}.camera-btn.secondary{background:#eef2fb;color:#35425d}.camera-btn:disabled{opacity:.45;cursor:not-allowed}.camera-status{font-size:12px;color:#738097;margin-top:10px}.camera-preview{display:none;margin-top:12px;border:1px solid #cce9dc;border-radius:9px;padding:10px;color:#176b4b;background:#f0fbf6;font-size:12px}.return-form-section{margin-top:18px}.return-label{display:block;font-size:13px;font-weight:750;color:#26334e;margin:17px 0 9px}.return-options{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:9px}.return-option{border:1px solid #e1e7f1;border-radius:10px;padding:12px;font-size:12px;color:#33415d;display:flex;gap:8px;align-items:center}.return-option input{accent-color:#1455d8}.return-checks{display:grid;gap:8px}.return-check{display:flex;gap:10px;align-items:flex-start;background:#f3f6fc;border-radius:9px;padding:11px 12px;font-size:12px;color:#35425e}.return-check input{accent-color:#1455d8;margin-top:2px}.return-notes{width:100%;min-height:85px;border:1px solid #dfe5ef;border-radius:10px;padding:12px;font:13px inherit;resize:vertical}.return-actions{display:flex;justify-content:space-between;gap:12px;margin-top:20px;padding-top:18px;border-top:1px solid #edf0f6}.return-submit{border:0;border-radius:10px;background:linear-gradient(100deg,#123d9e,#0ba6d6);color:#fff;padding:13px 18px;font-weight:750;cursor:pointer}.return-submit:disabled{opacity:.45;cursor:not-allowed}.return-back{display:inline-flex;align-items:center;padding:12px 15px;border-radius:10px;background:#eef1f7;color:#33415d;text-decoration:none;font-size:13px;font-weight:700}.return-footnote{font-size:11px;color:#7d8799;text-align:center;margin-top:12px;line-height:1.5}
@media(max-width:800px){.return-grid{grid-template-columns:1fr}.return-head{padding:21px}.return-card{padding:19px}.return-options{grid-template-columns:1fr}.return-actions{flex-direction:column-reverse}.return-actions>*{justify-content:center;text-align:center}.camera-frame{min-height:210px}}
</style>
<div class="return-page"><div class="return-wrap">
  <div class="return-breadcrumb"><a href="{{ route('dashboard') }}">Beranda</a>　/　<a href="{{ route('borrow.index') }}">Peminjaman</a>　/　Pengembalian Hardware</div>
  <header class="return-head">
    <span class="return-kicker">VERIFIKASI PENGEMBALIAN & TITIK PENYIMPANAN</span>
    <h1>Form Pengembalian & Inspeksi Hardware IoT</h1>
    <p>Pastikan perangkat sudah ditata kembali di lokasi penyimpanan. Ambil foto langsung dari kamera sebagai bukti sebelum mengirim laporan.</p>
    <div class="return-meta"><span>▣ {{ $loan->project_title }}</span><span>⌁ {{ $loan->serial_code }}</span><span>▤ Locker {{ $loan->locker_code ?: 'Belum ditentukan' }}</span><span>Jatuh tempo: {{ \Illuminate\Support\Carbon::parse($loan->due_date)->format('d M Y') }}</span></div>
  </header>
  @if(session('error'))<div class="return-alert">{{ session('error') }}</div>@endif
  @if($errors->any())<div class="return-alert">{{ $errors->first() }}</div>@endif
  <form id="returnForm" method="POST" action="{{ route('borrow.return',$loan->id) }}" enctype="multipart/form-data">
    @csrf
    <div class="return-grid">
      <aside class="return-card">
        <h2>Panduan Posisi Penataan Loker</h2><p class="return-sub">Ikuti langkah ini agar admin dapat memverifikasi perangkat dengan cepat.</p>
        <div class="return-steps">
          <div class="return-step"><b>1</b><div><strong>Posisikan unit di titik tengah</strong><p>Letakkan hardware pada slot atau alas yang sudah ditentukan di dalam locker.</p></div></div>
          <div class="return-step"><b>2</b><div><strong>Gulung kabel dengan rapi</strong><p>Pastikan kabel dan adaptor tidak kusut serta tidak menutupi komponen.</p></div></div>
          <div class="return-step"><b>3</b><div><strong>Pastikan komponen lengkap</strong><p>Periksa sensor, kabel, modul, dan aksesori sesuai paket peminjaman.</p></div></div>
          <div class="return-step"><b>4</b><div><strong>Pastikan kondisi aman</strong><p>Matikan sumber daya dan pastikan tidak ada kabel terkelupas atau komponen panas.</p></div></div>
        </div>
        <div class="return-warning"><strong>Perhatian:</strong> Foto harus memperlihatkan keseluruhan isi locker dan perangkat yang sudah dikembalikan. Jangan mengirim foto sebelum penataan selesai.</div>
      </aside>
      <div>
        <section class="return-card">
          <h2>📷 Foto Bukti Penataan Locker</h2><p class="return-sub">Wajib ambil foto langsung menggunakan kamera perangkat. Unggah dari galeri tidak disediakan pada form ini.</p>
          <div class="camera-frame" id="cameraFrame">
            <video id="cameraVideo" autoplay playsinline muted></video>
            <canvas id="captureCanvas" hidden></canvas>
            <div class="camera-placeholder" id="cameraPlaceholder"><span class="cam-icon">📷</span><strong>Kamera belum diaktifkan</strong><p style="font-size:12px;color:#aebbd0">Tekan tombol Buka Kamera untuk memulai.</p></div>
            <div class="camera-stamp" id="cameraStamp"></div>
          </div>
          <div class="camera-controls"><button type="button" class="camera-btn" id="startCamera">Buka Kamera</button><button type="button" class="camera-btn" id="takePhoto" disabled>Ambil Foto</button><button type="button" class="camera-btn secondary" id="retakePhoto" hidden>Foto Ulang</button></div>
          <input type="file" id="returnPhoto" name="return_photo" accept="image/jpeg,image/png" hidden>
          <input type="hidden" name="camera_captured" id="cameraCaptured" value="">
          <div class="camera-status" id="cameraStatus">Kamera membutuhkan izin akses. Gunakan HTTPS atau localhost agar browser mengizinkan kamera.</div>
          <div class="camera-preview" id="cameraPreview">✓ Foto berhasil diambil dari kamera dan siap dilampirkan. Timestamp ditampilkan pada foto.</div>
        </section>
        <section class="return-card return-form-section">
          <h2>Catatan Operasional & Kondisi Fungsional</h2><p class="return-sub">Laporkan kondisi aktual saat perangkat dikembalikan.</p>
          <label class="return-label">Status fungsional alat saat dikembalikan <span style="color:#d33">*</span></label>
          <div class="return-options">
            <label class="return-option"><input type="radio" name="return_condition" value="baik" required checked> Normal & berfungsi penuh</label>
            <label class="return-option"><input type="radio" name="return_condition" value="perlu_perbaikan" required> Kabel longgar / perlu perbaikan</label>
            <label class="return-option"><input type="radio" name="return_condition" value="rusak" required> Komponen rusak / hilang</label>
          </div>
          <label class="return-label">Pengecekan fungsional cepat (wajib centang semua)</label>
          <div class="return-checks">
            <label class="return-check"><input type="checkbox" name="return_checklist[locker_placed]" value="1" required> Perangkat sudah diletakkan kembali di dalam locker yang ditentukan.</label>
            <label class="return-check"><input type="checkbox" name="return_checklist[cables_arranged]" value="1" required> Kabel, adaptor, dan selang sudah digulung / ditata dengan rapi.</label>
            <label class="return-check"><input type="checkbox" name="return_checklist[components_complete]" value="1" required> Semua komponen dan aksesori sudah lengkap.</label>
            <label class="return-check"><input type="checkbox" name="return_checklist[power_safe]" value="1" required> Sumber daya sudah dimatikan dan perangkat aman disimpan.</label>
          </div>
          <label class="return-label" for="returnNotes">Keterangan & pengalaman penggunaan (opsional)</label>
          <textarea class="return-notes" id="returnNotes" name="return_notes" maxlength="1000" placeholder="Contoh: Modul digunakan selama 4 hari untuk uji coba. Semua komponen berfungsi normal..."></textarea>
        </section>
        <section class="return-card return-form-section">
          <h2>Timestamp & Verifikasi Pengembalian</h2><p class="return-sub">Foto diberi cap waktu saat diambil. Sistem juga menyimpan waktu penerimaan berdasarkan jam server sebagai catatan resmi.</p>
          <div class="return-meta"><span>Waktu perangkat: <strong id="deviceTime">Menunggu kamera</strong></span><span>Waktu server: dicatat otomatis saat dikirim</span></div>
          <div class="return-actions"><a class="return-back" href="{{ route('borrow.index') }}">← Batal / Kembali</a><button class="return-submit" id="submitReturn" type="submit" disabled>🔒 Kirim Bukti Pengembalian</button></div>
          <p class="return-footnote">Dengan mengirim form ini, kamu menyatakan perangkat telah ditata kembali dan foto yang dikirim merupakan bukti aktual. Jika kondisi tidak normal, unit akan otomatis ditandai untuk pemeliharaan.</p>
        </section>
      </div>
    </div>
  </form>
</div></div>
<script>
(() => {
  const video = document.getElementById('cameraVideo');
  const canvas = document.getElementById('captureCanvas');
  const photoInput = document.getElementById('returnPhoto');
  const captured = document.getElementById('cameraCaptured');
  const startBtn = document.getElementById('startCamera');
  const takeBtn = document.getElementById('takePhoto');
  const retakeBtn = document.getElementById('retakePhoto');
  const submitBtn = document.getElementById('submitReturn');
  const placeholder = document.getElementById('cameraPlaceholder');
  const stamp = document.getElementById('cameraStamp');
  const status = document.getElementById('cameraStatus');
  const preview = document.getElementById('cameraPreview');
  const deviceTime = document.getElementById('deviceTime');
  let stream = null;
  let hasCapture = false;
  const formatTime = date => new Intl.DateTimeFormat('id-ID', {dateStyle:'medium', timeStyle:'medium', hour12:false}).format(date);

  async function startCamera() {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      status.textContent = 'Browser tidak mendukung akses kamera. Buka halaman melalui HTTPS atau browser terbaru.';
      return;
    }
    try {
      if (stream) stream.getTracks().forEach(track => track.stop());
      stream = await navigator.mediaDevices.getUserMedia({video:{facingMode:{ideal:'environment'}, width:{ideal:1920}, height:{ideal:1080}}, audio:false});
      video.srcObject = stream;
      await video.play();
      video.hidden = false; canvas.hidden = true; placeholder.hidden = true;
      takeBtn.disabled = false; startBtn.textContent = 'Ganti Kamera';
      status.textContent = 'Kamera aktif. Arahkan ke locker dan seluruh perangkat, lalu ambil foto.';
      deviceTime.textContent = formatTime(new Date());
      hasCapture = false; captured.value = ''; photoInput.value = ''; submitBtn.disabled = true;
      preview.style.display = 'none'; stamp.style.display = 'none'; retakeBtn.hidden = true;
    } catch (err) {
      status.textContent = err.name === 'NotAllowedError' ? 'Izin kamera ditolak. Aktifkan izin kamera di pengaturan browser.' : 'Kamera tidak dapat dibuka. Pastikan kamera tidak sedang digunakan aplikasi lain.';
    }
  }

  function takePhoto() {
    if (!stream || !video.videoWidth) return;
    const width = video.videoWidth, height = video.videoHeight;
    canvas.width = width; canvas.height = height;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, width, height);
    const now = new Date();
    const line = 'IoT Project Hub  |  ' + formatTime(now);
    const pad = Math.max(14, Math.round(width * .012));
    const fontSize = Math.max(18, Math.round(width * .022));
    ctx.font = `600 ${fontSize}px monospace`;
    const boxH = fontSize + pad * 1.5;
    ctx.fillStyle = 'rgba(4,14,25,.78)'; ctx.fillRect(pad, height - boxH - pad, Math.min(width - pad*2, ctx.measureText(line).width + pad*1.5), boxH);
    ctx.fillStyle = '#ffffff'; ctx.fillText(line, pad*1.7, height - pad*1.65);
    canvas.toBlob(blob => {
      if (!blob) { status.textContent = 'Foto gagal diproses. Silakan coba lagi.'; return; }
      const file = new File([blob], `return-${Date.now()}.jpg`, {type:'image/jpeg'});
      const transfer = new DataTransfer(); transfer.items.add(file); photoInput.files = transfer.files;
      captured.value = '1'; hasCapture = true;
      video.hidden = true; canvas.hidden = false;
      stamp.textContent = line; stamp.style.display = 'block';
      status.textContent = 'Foto diambil pada ' + formatTime(now) + '.';
      deviceTime.textContent = formatTime(now);
      preview.style.display = 'block'; takeBtn.disabled = true; retakeBtn.hidden = false; submitBtn.disabled = false;
      if (stream) stream.getTracks().forEach(track => track.stop()); stream = null;
    }, 'image/jpeg', .9);
  }

  startBtn.addEventListener('click', startCamera);
  takeBtn.addEventListener('click', takePhoto);
  retakeBtn.addEventListener('click', startCamera);
  document.getElementById('returnForm').addEventListener('submit', event => {
    if (!hasCapture || !photoInput.files.length || captured.value !== '1') {
      event.preventDefault(); status.textContent = 'Foto kamera wajib diambil sebelum mengirim form.'; return;
    }
    submitBtn.disabled = true; submitBtn.textContent = 'Mengirim bukti...';
  });
  window.addEventListener('pagehide', () => { if (stream) stream.getTracks().forEach(track => track.stop()); });
})();
</script>
@endsection
