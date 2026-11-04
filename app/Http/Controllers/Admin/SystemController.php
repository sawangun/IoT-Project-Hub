<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SystemController extends Controller
{
    public function reports(): View
    {
        $metrics = [
            'borrowings'=>DB::table('borrowings')->count(),
            'returned'=>DB::table('borrowings')->where('status','returned')->count(),
            'overdue'=>DB::table('borrowings')->where('status','overdue')->count(),
            'lockers_total'=>DB::table('lockers')->count(),
            'lockers_available'=>DB::table('lockers')->where('status','available')->count(),
            'projects_published'=>DB::table('projects')->where('status','published')->count(),
            'projects_pending'=>DB::table('projects')->where('status','pending')->count(),
            'orders'=>DB::table('orders')->count(),
        ];
        $popularComponents=DB::table('order_items as oi')->join('components as c','c.id','=','oi.component_id')
            ->select('c.name',DB::raw('SUM(oi.qty) as quantity'))->groupBy('c.id','c.name')
            ->orderByDesc('quantity')->limit(5)->get();
        return view('admin.system.reports',compact('metrics','popularComponents'));
    }

    public function activity(): View
    {
        $logs=DB::table('activity_logs as a')->leftJoin('profiles as p','p.id','=','a.user_id')
            ->leftJoin('lockers as l','l.id','=','a.locker_id')
            ->select('a.*','p.name as user_name','l.code as locker_code')
            ->orderByDesc('a.created_at')->limit(100)->get();
        $total=DB::table('activity_logs')->count();
        return view('admin.system.activity',compact('logs','total'));
    }

    public function settings(): View
    {
        return view('admin.system.settings');
    }
}
