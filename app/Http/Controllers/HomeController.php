<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\View\View;

#[Middleware('auth')]
class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function index(): View
    {
        return view('home');
    }
}
