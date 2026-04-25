<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\SessionTracker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('student.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'cedula' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::guard('student')->attempt(['id' => $credentials['cedula'], 'password' => $credentials['password']])) {
            $student = Auth::guard('student')->user();

            $institutionActive = DB::table('groups')
                ->join('institutions', 'groups.institution_id', '=', 'institutions.id')
                ->where('groups.id', $student->group_id)
                ->value('institutions.active');

            if ($institutionActive === false) {
                Auth::guard('student')->logout();

                return back()->withErrors([
                    'cedula' => 'Tu institución no tiene acceso activo a ContaEdu. Contacta a tu coordinador.',
                ])->onlyInput('cedula');
            }

            $request->session()->regenerate();
            $request->session()->forget(['audit_mode', 'audit_tenant_id', 'audit_student_name']);

            app(SessionTracker::class)->startStudent($student, $request);

            return redirect()->route('student.dashboard');
        }

        return back()->withErrors([
            'cedula' => 'Las credenciales no coinciden con nuestros registros.',
        ])->onlyInput('cedula');
    }

    public function logout(Request $request): RedirectResponse
    {
        $student = Auth::guard('student')->user();

        if ($student) {
            app(SessionTracker::class)->endStudent($student->id);
        }

        Auth::guard('student')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('student.login');
    }
}
