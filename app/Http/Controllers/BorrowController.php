<?php

namespace App\Http\Controllers;

use App\Models\Borrowing;
use App\Support\HubData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BorrowController extends Controller
{
    public function index(): View
    {
        return view('borrow.index', [
            'steps'    => HubData::steps(),
            'projects' => HubData::projects(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $slugs = HubData::projects()->pluck('slug')->all();

        $data = $request->validate([
            'project_slug' => ['required', 'in:'.implode(',', $slugs)],
        ]);

        $borrowing = Borrowing::create([
            'user_id'      => $request->user()->id,
            'project_slug' => $data['project_slug'],
            'token'        => Str::random(40),
            'expires_at'   => now()->addMinutes(30), // QR berlaku 30 menit
            'status'       => 'pending',
        ]);

        return redirect()->route('borrow.qr', $borrowing);
    }

    public function qr(Borrowing $borrowing): View
    {
        abort_unless($borrowing->user_id === auth()->id(), 403);

        return view('borrow.qr', [
            'borrowing' => $borrowing,
            'project'   => HubData::projects()->firstWhere('slug', $borrowing->project_slug),
        ]);
    }
}
