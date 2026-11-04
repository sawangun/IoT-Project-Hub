<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProjectManagementController extends Controller
{
    private function rows()
    {
        return DB::table('projects')->orderByDesc('created_at')->get()->map(fn ($p) => [
            'id' => (int) $p->id,
            'title' => $p->title,
            'slug' => Str::slug($p->title).'-'.$p->id,
            'category' => $p->category ?? 'IoT Umum',
            'team' => $p->team ?? '—',
            'year' => $p->year ?? optional($p->created_at ? \Carbon\Carbon::parse($p->created_at) : null)->year,
            'level' => ['pemula'=>'Pemula','menengah'=>'Menengah','mahir'=>'Lanjutan'][$p->difficulty] ?? 'Pemula',
            'description' => $p->description ?? '',
            'hardware' => $p->hardware ?? '',
            'locker' => $p->locker ?? 'Belum dialokasikan',
            'status' => $p->status === 'pending' ? 'draft' : $p->status,
            'image' => $p->image_url ?? '',
            'repo_url' => $p->repo_url ?? '',
            'schematic_url' => $p->schematic_url ?? '',
            'wiring_guide' => $p->wiring_guide ?? '',
            'maker_name' => $p->maker_name ?? '',
            'maker_instagram_url' => $p->maker_instagram_url ?? '',
            'maker_github_url' => $p->maker_github_url ?? '',
            'maker_linkedin_url' => $p->maker_linkedin_url ?? '',
            'updated_at' => $p->updated_at,
        ]);
    }

    public function index(Request $request): View
    {
        $all = $this->rows();
        $q = trim((string) $request->query('q', ''));
        $category = (string) $request->query('category', '');
        $status = (string) $request->query('status', '');
        $projects = $all;
        if ($q !== '') $projects = $projects->filter(fn ($p) => str_contains(Str::lower($p['title'].' '.$p['team'].' '.$p['hardware']), Str::lower($q)));
        if ($category !== '') $projects = $projects->where('category', $category);
        if ($status !== '') $projects = $projects->where('status', $status);

        return view('admin.projects.index', [
            'projects'=>$projects->values(), 'q'=>$q, 'category'=>$category, 'status'=>$status,
            'totalProjects'=>$all->count(), 'publishedProjects'=>$all->where('status','published')->count(),
            'draftProjects'=>$all->where('status','draft')->count(), 'categories'=>$all->pluck('category')->unique()->values(),
        ]);
    }

    public function create(): View { return view('admin.projects.form', ['project'=>null, 'mode'=>'create']); }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['creator_id'] = auth()->id();
        $data['status'] = $this->dbStatus($request->input('save_action'), $data['status']);
        $data['difficulty'] = $this->difficulty($data['level']);
        $data['image_url'] = $request->hasFile('image_file')
            ? $request->file('image_file')->store('projects', 'public')
            : ($data['image_url'] ?? null);
        unset($data['level'], $data['image_file']);
        $id = DB::table('projects')->insertGetId($this->dbFields($data) + [
            'created_at'=>now(), 'updated_at'=>now(),
        ]);
        return redirect()->route('admin.projects.index')->with('success', "Proyek #{$id} berhasil disimpan ke Supabase.");
    }

    public function edit(int $project): View
    {
        $item = $this->rows()->firstWhere('id', $project);
        abort_if(!$item, 404);
        return view('admin.projects.form', ['project'=>$item, 'mode'=>'edit']);
    }

    public function update(Request $request, int $project): RedirectResponse
    {
        $existing = DB::table('projects')->where('id', $project)->first();
        abort_if(!$existing, 404);
        $data = $this->validated($request);
        $data['status'] = $this->dbStatus($request->input('save_action'), $data['status']);
        $data['difficulty'] = $this->difficulty($data['level']);

        if ($request->hasFile('image_file')) {
            $oldImage = $existing->image_url ?? null;
            if ($oldImage && !preg_match('~^https?://~i', $oldImage)) {
                Storage::disk('public')->delete(ltrim($oldImage, '/'));
            }
            $data['image_url'] = $request->file('image_file')->store('projects', 'public');
        } elseif (!empty($data['image_url'])) {
            $oldImage = $existing->image_url ?? null;
            if ($oldImage && !preg_match('~^https?://~i', $oldImage) && $oldImage !== $data['image_url']) {
                Storage::disk('public')->delete(ltrim($oldImage, '/'));
            }
        } else {
            $data['image_url'] = $existing->image_url ?? null;
        }
        unset($data['level'], $data['image_file']);
        $updated = DB::table('projects')->where('id',$project)->update($this->dbFields($data) + ['updated_at'=>now()]);
        abort_if(!$updated && !DB::table('projects')->where('id',$project)->exists(),404);
        return redirect()->route('admin.projects.index')->with('success','Perubahan proyek berhasil disimpan ke Supabase.');
    }

    public function destroy(int $project): RedirectResponse
    {
        $deleted = DB::table('projects')->where('id',$project)->delete();
        abort_if(!$deleted,404);
        return redirect()->route('admin.projects.index')->with('success','Proyek berhasil dihapus dari Supabase.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title'=>['required','string','max:120'], 'category'=>['required','string','max:60'],
            'team'=>['nullable','string','max:80'], 'year'=>['nullable','integer','min:2000','max:2100'],
            'level'=>['required','in:Pemula,Menengah,Lanjutan,Expert'],
            'description'=>['required','string','max:700'], 'hardware'=>['nullable','string','max:240'],
            'locker'=>['nullable','string','max:40'], 'status'=>['required','in:draft,published'],
            'image_url'=>['nullable','url','max:500'],
            'repo_url'=>['nullable','url','max:500'], 'schematic_url'=>['nullable','url','max:500'],
            'wiring_guide'=>['nullable','string','max:5000'],
            'maker_name'=>['nullable','string','max:120'],
            'maker_instagram_url'=>['nullable','url','max:500'], 'maker_github_url'=>['nullable','url','max:500'], 'maker_linkedin_url'=>['nullable','url','max:500'],
            'image_file'=>['nullable','image','mimes:jpg,jpeg,png,webp','max:5120'],
        ]);
    }

    private function dbStatus(?string $action, string $status): string
    {
        if ($action === 'draft' || $status === 'draft') return 'pending';
        return 'published';
    }

    private function difficulty(string $level): string
    {
        return match ($level) {'Pemula'=>'pemula','Menengah'=>'menengah',default=>'mahir'};
    }

    private function dbFields(array $data): array
    {
        return [
            'title'=>$data['title'], 'description'=>$data['description'],
            'difficulty'=>$data['difficulty'], 'status'=>$data['status'],
            'category'=>$data['category'], 'team'=>$data['team'] ?? null,
            'year'=>$data['year'] ?? null, 'hardware'=>$data['hardware'] ?? null,
            'locker'=>$data['locker'] ?? null, 'image_url'=>$data['image_url'] ?: null,
            'repo_url'=>$data['repo_url'] ?: null, 'schematic_url'=>$data['schematic_url'] ?: null,
            'wiring_guide'=>$data['wiring_guide'] ?: null, 'maker_name'=>$data['maker_name'] ?: null,
            'maker_instagram_url'=>$data['maker_instagram_url'] ?: null, 'maker_github_url'=>$data['maker_github_url'] ?: null,
            'maker_linkedin_url'=>$data['maker_linkedin_url'] ?: null,
        ];
    }
}
