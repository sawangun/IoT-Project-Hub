@extends('layouts.admin')
@section('title', 'Pesanan Pembelian')
@section('content')
<div class="container-fluid py-4">
 <div class="d-flex flex-wrap justify-content-between align-items-center mb-4"><div><span class="text-muted small">MANAJEMEN TRANSAKSI</span><h1 class="h3 mb-1">Pesanan Kit & Komponen</h1><p class="text-muted mb-0">Pantau pesanan dan perbarui status pemenuhan.</p></div></div>
 @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
 @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
 <div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>No. Pesanan</th><th>Pemesan</th><th>Item</th><th>Jumlah</th><th>Total</th><th>Metode</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
 @forelse($orders as $order)
 <tr><td>#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}<br><small class="text-muted">{{ \Illuminate\Support\Carbon::parse($order->created_at)->format('d/m/Y H:i') }}</small></td><td>{{ $order->customer_name }}<br><small>{{ $order->customer_email }}</small></td><td>{{ $order->item_name }}<br><small>{{ ucfirst($order->item_type) }}</small></td><td>{{ $order->quantity }}</td><td>Rp{{ number_format((float)$order->total, 0, ',', '.') }}</td><td>{{ strtoupper(str_replace('_', ' ', $order->payment_method)) }}<br><small class="text-muted">{{ $order->status === 'paid' ? 'Simulasi lunas' : 'Mode demo' }}</small></td><td><span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span></td><td>
 @if(in_array($order->status, ['paid','processing'], true))
 <form method="POST" action="{{ route('admin.purchase-orders.status', $order->id) }}" class="d-flex gap-1">@csrf @method('PATCH')<select name="status" class="form-select form-select-sm"><option value="processing" @selected($order->status==='processing')>Diproses</option><option value="completed">Selesai</option><option value="cancelled">Dibatalkan</option></select><button class="btn btn-sm btn-primary" type="submit">Simpan</button></form>
 @else <span class="text-muted small">—</span> @endif
 </td></tr>
 @empty <tr><td colspan="8" class="text-center text-muted py-4">Belum ada pesanan pembelian.</td></tr>@endforelse
 </tbody></table></div></div>
</div>
@endsection
