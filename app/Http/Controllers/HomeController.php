<?php

namespace App\Http\Controllers;

use App\Support\HubData;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('home', [
            'stats'        => HubData::stats(),
            'lockers'      => HubData::lockers(),
            'features'     => HubData::features(),
            'projects'     => HubData::projects(),
            'steps'        => HubData::steps(),
            'testimonials' => HubData::testimonials(),
        ]);
    }
}
