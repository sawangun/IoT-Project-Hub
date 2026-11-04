<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function index(): View
    {
        $orders = DB::table('orders as o')
            ->join('profiles as p','p.id','=','o.user_id')
            ->leftJoin('order_items as oi','oi.order_id','=','o.id')
            ->leftJoin('kits as k','k.id','=','oi.kit_id')
            ->leftJoin('components as c','c.id','=','oi.component_id')
            ->select('o.id','o.created_at','o.total','o.payment_method','o.status',
                'p.name as customer_name','p.email as customer_email',
                DB::raw("string_agg(coalesce(k.name,c.name,'Item'), ', ') as item_name"),
                DB::raw('coalesce(sum(oi.qty),0)::int as quantity'))
            ->groupBy('o.id','o.created_at','o.total','o.payment_method','o.status','p.name','p.email')
            ->orderByDesc('o.created_at')->get();
        return view('admin.purchase-orders.index', compact('orders'));
    }

    public function updateStatus(Request $request, int $order): RedirectResponse
    {
        $data = $request->validate(['status'=>['required','in:processing,ready_pickup,completed,cancelled']]);
        $purchase = DB::table('orders')->where('id',$order)->first();
        abort_if(!$purchase,404);
        abort_unless(in_array($purchase->status,['paid','processing','ready_pickup'],true),422,'Pesanan harus sudah dibayar sebelum diproses.');
        DB::table('orders')->where('id',$order)->update(['status'=>$data['status'],'updated_at'=>now()]);
        return back()->with('success','Status pesanan berhasil diperbarui di Supabase.');
    }
}
