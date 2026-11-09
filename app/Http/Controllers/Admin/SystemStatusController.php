<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SystemHealthService;
use Illuminate\View\View;

class SystemStatusController extends Controller
{
    public function __invoke(SystemHealthService $service): View
    {
        $health = $service->checkHealth();

        return view('admin.system.status', compact('health'));
    }
}
