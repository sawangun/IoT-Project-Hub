<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $difficulty = (string) $request->query('difficulty', '');
        $projects = collect();
        if (Schema::hasTable('projects')) {
            $query = DB::table('projects')->where('status', 'published');
            if ($q !== '') $query->where(function ($builder) use ($q) {
                $builder->where('title', 'ilike', "%{$q}%")->orWhere('description', 'ilike', "%{$q}%");
            });
            if (in_array($difficulty, ['pemula', 'menengah', 'mahir'], true)) $query->where('difficulty', $difficulty);
            $projects = $query->orderByDesc('created_at')->paginate(9)->withQueryString();
        }
        $savedIds = collect();
        if (auth()->check() && Schema::hasTable('saved_projects')) {
            $savedIds = DB::table('saved_projects')->where('user_id', auth()->id())->pluck('project_id')->all();
        }
        return view('projects.index', compact('projects', 'q', 'difficulty', 'savedIds'));
    }

    public function show(int $project): View
    {
        abort_unless(Schema::hasTable('projects'), 404);
        $project = DB::table('projects')->where('id', $project)->where('status', 'published')->first();
        abort_if(! $project, 404);

        $components = collect();
        $kits = collect();
        $units = collect();
        if (Schema::hasTable('project_components') && Schema::hasTable('components')) {
            $components = DB::table('project_components as pc')->join('components as c', 'c.id', '=', 'pc.component_id')
                ->where('pc.project_id', $project->id)->select('c.name', 'c.spec', 'c.price', 'c.stock', 'pc.qty')->orderBy('c.name')->get();
        }
        if (Schema::hasTable('kits')) $kits = DB::table('kits')->where('project_id', $project->id)->orderBy('name')->get();
        if (Schema::hasTable('hardware_units')) $units = DB::table('hardware_units')->where('project_id', $project->id)->select('status')->get();
        $isSaved = auth()->check() && Schema::hasTable('saved_projects')
            ? DB::table('saved_projects')->where('user_id', auth()->id())->where('project_id', $project->id)->exists()
            : false;
        return view('projects.show', compact('project', 'components', 'kits', 'units', 'isSaved'));
    }

    public function saved(): View
    {
        abort_unless(Schema::hasTable('saved_projects'), 503, 'Fitur proyek disukai belum tersedia. Jalankan skema database terlebih dahulu.');
        $projects = DB::table('projects as p')
            ->join('saved_projects as sp', 'sp.project_id', '=', 'p.id')
            ->where('sp.user_id', auth()->id())
            ->where('p.status', 'published')
            ->select('p.*')
            ->orderByDesc('sp.saved_at')->paginate(9);
        $savedIds = $projects->getCollection()->pluck('id')->all();
        return view('projects.saved', compact('projects', 'savedIds'));
    }

    public function toggleSaved(int $project): \Illuminate\Http\RedirectResponse
    {
        abort_unless(Schema::hasTable('saved_projects'), 503, 'Fitur proyek disukai belum tersedia. Jalankan skema database terlebih dahulu.');
        abort_unless(Schema::hasTable('projects') && DB::table('projects')->where('id', $project)->where('status', 'published')->exists(), 404);
        $existing = DB::table('saved_projects')->where('user_id', auth()->id())->where('project_id', $project)->exists();
        if ($existing) {
            DB::table('saved_projects')->where('user_id', auth()->id())->where('project_id', $project)->delete();
            $message = 'Proyek dihapus dari daftar disukai.';
        } else {
            DB::table('saved_projects')->insertOrIgnore(['user_id' => auth()->id(), 'project_id' => $project, 'saved_at' => now()]);
            $message = 'Proyek ditambahkan ke daftar disukai.';
        }
        return back()->with('success', $message);
    }
}
