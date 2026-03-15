<?php

namespace App\Http\Controllers\Admin;

use App\Models\Central\Institution;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $institutionsCount = Institution::count();
        $teachersCount = User::where('role', 'teacher')->count();

        return view('admin.dashboard', compact('institutionsCount', 'teachersCount'));
    }
}
