<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ImpersonationController extends Controller
{
    public function startStudent(string $tenantId): RedirectResponse
    {
        $tenant = Tenant::findOrFail($tenantId);

        session([
            'impersonating_admin_id' => auth('web')->id(),
            'impersonating_type' => 'student',
            'impersonating_name' => $tenant->student_name ?? $tenant->id,
        ]);

        auth('web')->logout();
        auth('student')->login($tenant);

        return redirect()->route('student.dashboard');
    }

    public function startTeacher(int $userId): RedirectResponse
    {
        $teacher = User::where('id', $userId)->where('role', 'teacher')->firstOrFail();

        session([
            'impersonating_admin_id' => auth('web')->id(),
            'impersonating_type' => 'teacher',
            'impersonating_name' => $teacher->name,
        ]);

        auth('web')->login($teacher);

        return redirect()->route('teacher.dashboard');
    }

    public function stop(Request $request): RedirectResponse
    {
        $adminId = session('impersonating_admin_id');

        auth('student')->logout();
        auth('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($adminId) {
            auth('web')->loginUsingId($adminId);
        }

        return redirect()->route('admin.dashboard');
    }
}
