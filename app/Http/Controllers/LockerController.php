<?php

namespace App\Http\Controllers;

use App\Support\HubData;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class LockerController extends Controller
{
    public function index(): View
    {
        return view('lockers.index', ['lockers' => HubData::lockers()]);
    }

    /** Dipanggil via fetch() tiap beberapa detik untuk panel "Telemetri Ketersediaan Loker". */
    public function status(): JsonResponse
    {
        // TODO: ganti dengan data real-time dari MQTT / tabel sensor.
        $lockers = collect(HubData::lockers())->map(fn ($l) => $l + [
            'percent' => (int) round($l['ready'] / $l['total'] * 100),
        ]);

        return response()->json([
            'live'       => true,
            'updated_at' => now()->toIso8601String(),
            'lockers'    => $lockers,
        ]);
    }
}
