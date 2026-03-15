<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $teacher = auth()->user();
        $group = $teacher->group()->with('tenants')->first();

        return view('teacher.dashboard', compact('teacher', 'group'));
    }
}
