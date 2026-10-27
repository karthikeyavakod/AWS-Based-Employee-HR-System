<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(DashboardService $dashboardService): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user?->hasRole('employee') && ! $user->hasAnyRole(['super-admin', 'hr-manager'])) {
            return redirect()->route('employee.dashboard');
        }

        $stats = $dashboardService->getStats();

        return view('dashboard.index', compact('stats'));
    }
}
