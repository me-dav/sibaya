<?php

namespace App\Http\Controllers;

use App\Services\StudentDashboardService;
use App\Support\Labels;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request, StudentDashboardService $service): View|RedirectResponse
    {
        $user = $request->user();

        // Admin punya dashboard sendiri.
        if (Labels::value($user->role) === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return view('dashboard', ['user' => $user] + $service->data($user));
    }
}
