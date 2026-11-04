<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PageController extends Controller
{
    public function components(): View
    {
        return view('pages.components');
    }

    public function about(): View
    {
        return view('pages.about');
    }
}
