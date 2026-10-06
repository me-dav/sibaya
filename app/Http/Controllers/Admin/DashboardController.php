<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminStatisticsService;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(AdminStatisticsService $stats): View
    {
        return view('admin.dashboard', [
            'summary'     => $stats->summary(),
            'evaluations' => $stats->latestEvaluations(),
            'results'     => $stats->recentResults(),
        ]);
    }
}
