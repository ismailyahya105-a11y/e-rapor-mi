<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class WaliDashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): View
    {
        return view('wali.dashboard');
    }
}
