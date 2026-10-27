<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(DashboardService $dashboardService): View
    {
        $stats = $dashboardService->getStats();

        return view('dashboard.index', compact('stats'));
    }
}
