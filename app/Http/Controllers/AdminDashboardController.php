<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'adminCount' => User::query()->where('role', 'admin')->count(),
            'waliCount' => User::query()->where('role', 'wali')->count(),
            'userCount' => User::query()->count(),
        ]);
    }
}
