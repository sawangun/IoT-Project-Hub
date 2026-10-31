<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\HubData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProjectManagementController extends Controller
{
    private function projects(): array
    {
        if (! session()->has('admin_projects')) {
            $seed = HubData::projects()->values()->map(function ($project, $index) {
                return [
                    'id' => $index + 1,
                    'title' => $project['title'],
                    'slug' => $project['slug'],
                    'category' => ['Smart Agriculture', 'Weather & Environment', 'Smart City', 'Healthcare'][$index] ?? 'IoT Umum',
                    'team' => ['RoboGrow Team', 'Lab Telekomunikasi', 'Lab AI', 'Lab Biomedika'][$index] ?? 'Tim Riset',
                    'year' => 2026,
                    'level' => ['Menengah', 'Lanjutan', 'Lanjutan', 'Pemula'][$index] ?? 'Menengah',
                    'description' => $project['desc'],
                    'hardware' => implode(', ', $project['tags']),
                    'locker' => $project['locker'],
                    'status' => 'published',
                    'image' => $project['image'] ?? '',
                    'updated_at' => now()->subDays($index + 1)->format('Y-m-d'),
                ];
            })->all();
            session(['admin_projects' => $seed]);
        }

        return session('admin_projects', []);
    }

    public function index(Request $request): View
    {
        $projects = collect($this->projects());
        $q = trim((string) $request->query('q', ''));
        $category = (string) $request->query('category', '');
        $status = (string) $request->query('status', '');

        if ($q !== '') {
            $needle = Str::lower($q);
            $projects = $projects->filter(fn ($project) => str_contains(Str::lower($project['title'].' '.$project['team'].' '.$project['hardware']), $needle));
        }
        if ($category !== '') $projects = $projects->where('category', $category);
        if ($status !== '') $projects = $projects->where('status', $status);

        $all = collect($this->projects());
        return view('admin.projects.index', [
            'projects' => $projects->values(), 'q' => $q, 'category' => $category, 'status' => $status,
            'totalProjects' => $all->count(), 'publishedProjects' => $all->where('status', 'published')->count(),
            'draftProjects' => $all->where('status', 'draft')->count(),
            'categories' => $all->pluck('category')->unique()->values(),
        ]);
    }

    public function create(): View
    {
        return view('admin.projects.form', ['project' => null, 'mode' => 'create']);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        if (in_array($request->input('save_action'), ['draft', 'publish'], true)) {
            $data['status'] = $request->input('save_action') === 'draft' ? 'draft' : 'published';
        }
        $projects = $this->projects();
        $data['id'] = (count($projects) ? max(array_column($projects, 'id')) : 0) + 1;
        $data['slug'] = Str::slug($data['title']).'-'.$data['id'];
        $data['updated_at'] = now()->format('Y-m-d');
        array_unshift($projects, $data);
        session(['admin_projects' => $projects]);

        return redirect()->route('admin.projects.index')->with('success', 'Proyek baru berhasil ditambahkan. Data tersimpan sementara di sesi browser/server, belum ke database.');
    }

    public function edit(int $project): View
    {
        $item = collect($this->projects())->firstWhere('id', $project);
        abort_if(! $item, 404);
        return view('admin.projects.form', ['project' => $item, 'mode' => 'edit']);
    }

    public function update(Request $request, int $project): RedirectResponse
    {
        $data = $this->validated($request);
        if ($request->input('save_action') === 'draft') $data['status'] = 'draft';
        if ($request->input('save_action') === 'publish') $data['status'] = 'published';
        $projects = $this->projects();
        $found = false;
        foreach ($projects as &$item) {
            if ($item['id'] === $project) {
                $data['id'] = $project;
                $data['slug'] = $item['slug'];
                $data['updated_at'] = now()->format('Y-m-d');
                $item = $data;
                $found = true;
                break;
            }
        }
        unset($item);
        abort_if(! $found, 404);
        session(['admin_projects' => $projects]);
        return redirect()->route('admin.projects.index')->with('success', 'Perubahan proyek berhasil disimpan sementara.');
    }

    public function destroy(int $project): RedirectResponse
    {
        $projects = array_values(array_filter($this->projects(), fn ($item) => $item['id'] !== $project));
        session(['admin_projects' => $projects]);
        return redirect()->route('admin.projects.index')->with('success', 'Proyek dihapus dari daftar sementara.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'category' => ['required', 'string', 'max:60'],
            'team' => ['required', 'string', 'max:80'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'level' => ['required', 'in:Pemula,Menengah,Lanjutan,Expert'],
            'description' => ['required', 'string', 'max:700'],
            'hardware' => ['required', 'string', 'max:240'],
            'locker' => ['nullable', 'string', 'max:40'],
            'status' => ['required', 'in:draft,published'],
            'image' => ['nullable', 'url', 'max:500'],
        ]);
        $data['locker'] = $data['locker'] ?: 'Belum dialokasikan';
        $data['image'] = $data['image'] ?? '';
        return $data;
    }
}
