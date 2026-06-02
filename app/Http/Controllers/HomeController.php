<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\View\View;

class HomeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            'auth',
        ];
    }

    /**
     * Show the application dashboard.
     */
    public function index(): View
    {
        return view('home');
    }
}
