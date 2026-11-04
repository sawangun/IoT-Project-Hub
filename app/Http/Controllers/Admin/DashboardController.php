<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $adminName = auth()->user()->name ?? 'Admin';
        $totalProjects = DB::table('projects')->where('status','published')->count();
        $totalBorrowings = DB::table('borrowings')->count();
        $activeBorrowings = DB::table('borrowings')->whereIn('status',['active','overdue'])->count();
        $pendingBorrowings = DB::table('borrowings')->where('status','pending')->count();

        $lockerStats = [
            'total'=>DB::table('lockers')->count(),
            'available'=>DB::table('lockers')->where('status','available')->count(),
            'occupied'=>DB::table('lockers')->where('status','occupied')->count(),
            'maintenance'=>DB::table('lockers')->where('status','maintenance')->count(),
            'offline'=>DB::table('lockers')->where('status','offline')->count(),
        ];
        $lockers = DB::table('lockers')->orderBy('code')->limit(8)->get()->map(fn($l)=>[
            'code'=>$l->code,'name'=>$l->code,'status'=>$l->status,
        ])->all();

        $start = now()->startOfDay()->subDays(6);
        $daily = DB::table('borrowings')->where('created_at','>=',$start)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupByRaw('DATE(created_at)')->pluck('total','day');
        $weeklyChart = collect(range(0,6))->map(function($offset) use($start,$daily){
            $date=$start->copy()->addDays($offset);
            return ['label'=>$date->translatedFormat('D'),'total'=>(int)($daily[$date->toDateString()] ?? 0)];
        })->all();

        $recentBorrowings = DB::table('borrowings as b')
            ->join('profiles as u','u.id','=','b.user_id')
            ->join('hardware_units as hu','hu.id','=','b.unit_id')
            ->join('projects as p','p.id','=','hu.project_id')
            ->select('b.id','u.name as user_name','p.title as project_title','b.created_at as request_date','b.due_date as expires_date','b.status')
            ->orderByDesc('b.created_at')->limit(6)->get()->map(fn($r)=>(array)$r)->all();

        $kits = DB::table('kits')->orderByDesc('stock')->limit(5)->get()->map(fn($k)=>[
            'name'=>$k->name,'detail'=>$k->description ?? 'Paket perangkat IoT',
            'price'=>'Rp'.number_format((float)$k->price,0,',','.'),
            'stock'=>(int)$k->stock,'status'=>$k->stock <= 2 ? 'Stok menipis' : 'Tersedia',
        ])->all();

        return view('admin.dashboard',compact(
            'adminName','totalProjects','totalBorrowings','activeBorrowings','pendingBorrowings',
            'lockerStats','lockers','weeklyChart','recentBorrowings','kits'
        ));
    }
}
