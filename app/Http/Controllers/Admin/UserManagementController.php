<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    private function userRows()
    {
        return DB::table('profiles as p')
            ->leftJoin('borrowings as b','b.user_id','=','p.id')
            ->leftJoin('projects as pr','pr.creator_id','=','p.id')
            ->select('p.id','p.name','p.email','p.nim','p.phone','p.role','p.is_verified',
                DB::raw('COUNT(DISTINCT b.id) as borrowing_count'),
                DB::raw('COUNT(DISTINCT pr.id) as project_count'))
            ->groupBy('p.id','p.name','p.email','p.nim','p.phone','p.role','p.is_verified')
            ->orderByDesc('p.created_at')->get()->map(fn($u)=>[
                'id'=>$u->id,'initial'=>strtoupper(substr($u->name ?? 'U',0,2)),
                'name'=>$u->name,'identity'=>$u->nim ?: '—','role'=>ucfirst($u->role),
                'program'=>'—','semester'=>'—','rfid'=>'Belum terdaftar',
                'locker'=>'Akses sesuai status akun','project'=>'—',
                'project_meta'=>$u->project_count.' proyek terdaftar',
                'status'=>$u->is_verified ? 'Terverifikasi' : 'Belum verifikasi',
                'email'=>$u->email,'phone'=>$u->phone ?: '—','advisor'=>'—',
                'borrowing_count'=>(int)$u->borrowing_count,
            ]);
    }

    public function index(): View
    {
        $users=$this->userRows();
        return view('admin.users.index',[
            'users'=>$users,'totalUsers'=>$users->count(),
            'verifiedUsers'=>DB::table('profiles')->where('is_verified',true)->count(),
            'researchProjects'=>DB::table('projects')->whereIn('status',['pending','published'])->count(),
            'overdueBorrowings'=>DB::table('borrowings')->where('status','overdue')->count(),
            'studentUsers'=>DB::table('profiles')->where('role','mahasiswa')->count(),
            'generalUsers'=>DB::table('profiles')->where('role','umum')->count(),
            'adminUsers'=>DB::table('profiles')->where('role','admin')->count(),
        ]);
    }

    public function create(): View { return view('admin.users.create'); }

    public function store(Request $request)
    {
        $data=$request->validate([
            'name'=>['required','string','max:120'],
            'identity'=>['nullable','string','max:50'],
            'email'=>['required','email','max:160'],
            'phone'=>['nullable','string','max:30'],
            'role'=>['required','in:mahasiswa,umum'],
            'password'=>['required','string','min:8','max:72'],
        ]);
        $url=rtrim((string)config('services.supabase.url'),'/');
        $serviceKey=(string)config('services.supabase.service_role_key');
        abort_if(!$url || !$serviceKey,503,'SUPABASE_URL dan SUPABASE_SERVICE_ROLE_KEY harus diatur untuk membuat akun dari admin.');
        $response=Http::withHeaders([
            'apikey'=>$serviceKey,'Authorization'=>'Bearer '.$serviceKey,
            'Accept'=>'application/json','Content-Type'=>'application/json',
        ])->timeout(20)->post($url.'/auth/v1/admin/users',[
            'email'=>$data['email'],'password'=>$data['password'],'email_confirm'=>true,
            'user_metadata'=>[
                'name'=>$data['name'],'nim'=>$data['identity'] ?? '',
                'phone'=>$data['phone'] ?? '','role'=>$data['role'],
            ],
        ]);
        if(!$response->successful()){
            return back()->withErrors(['email'=>$response->json('msg') ?? $response->json('message') ?? 'Supabase Auth gagal membuat akun.'])
                ->withInput($request->except('password'));
        }
        return redirect()->route('admin.users.index')->with('success','Akun berhasil dibuat melalui Supabase Auth. Profil dibuat otomatis oleh trigger database.');
    }

    public function show(string $user): View
    {
        $profile=DB::table('profiles')->where('id',$user)->first();
        abort_if(!$profile,404);
        $borrowings=DB::table('borrowings')->where('user_id',$user)->orderByDesc('created_at')->limit(10)->get();
        $projects=DB::table('projects')->where('creator_id',$user)->orderByDesc('created_at')->limit(10)->get();
        $row=[
            'id'=>$profile->id,'initial'=>strtoupper(substr($profile->name ?? 'U',0,2)),
            'name'=>$profile->name,'identity'=>$profile->nim ?: '—','role'=>ucfirst($profile->role),
            'program'=>'—','semester'=>'—','rfid'=>'Belum terdaftar','locker'=>'Akses sesuai status akun',
            'project'=>$projects->first()->title ?? 'Belum ada proyek','project_meta'=>$projects->count().' proyek',
            'status'=>$profile->is_verified ? 'Terverifikasi' : 'Belum verifikasi',
            'email'=>$profile->email,'phone'=>$profile->phone ?: '—','advisor'=>'—',
            'borrowing_count'=>$borrowings->count(),'borrowings'=>$borrowings,'projects'=>$projects,
        ];
        return view('admin.users.show',['user'=>$row]);
    }

    public function profile(): View { return view('admin.profile'); }
    public function help(): View { return view('admin.help'); }
}
