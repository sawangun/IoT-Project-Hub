<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function index(): View
    {
        $orders = Schema::hasTable('purchase_orders')
            ? DB::table('purchase_orders')->orderByDesc('created_at')->get()
            : collect();
        return view('admin.purchase-orders.index', compact('orders'));
    }

    public function updateStatus(Request $request, int $order): RedirectResponse
    {
        abort_unless(Schema::hasTable('purchase_orders'), 503, 'Tabel pesanan belum tersedia.');
        $data = $request->validate(['status' => ['required', 'in:processing,completed,cancelled']]);
        DB::transaction(function () use ($order, $data) {
            $purchase = DB::table('purchase_orders')->where('id', $order)->lockForUpdate()->first();
            abort_unless($purchase, 404);
            abort_unless(in_array($purchase->status, ['paid', 'processing'], true), 422,
                'Pesanan harus berstatus Lunas sebelum diproses.');
            if ($data['status'] === 'cancelled') {
                $table = $purchase->item_type === 'kit' ? 'kits' : 'components';
                if (Schema::hasTable($table)) {
                    DB::table($table)->where('id', $purchase->item_id)->increment('stock', (int) $purchase->quantity);
                }
            }
            DB::table('purchase_orders')->where('id', $order)->update([
                'status' => $data['status'], 'updated_at' => now(),
            ]);
        });
        return back()->with('success', 'Status pesanan berhasil diperbarui.');
    }
}
