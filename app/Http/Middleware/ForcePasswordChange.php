<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        $webUser = auth('web')->user();
        if ($webUser && $webUser->must_change_password) {
            if (! $request->routeIs('password.force-change', 'password.force-change.update', 'logout')) {
                return redirect()->route('password.force-change');
            }
        }

        $student = auth('student')->user();
        if ($student && $student->must_change_password) {
            if (! $request->routeIs('student.password.force-change', 'student.password.force-change.update', 'student.logout')) {
                return redirect()->route('student.password.force-change');
            }
        }

        return $next($request);
    }
}
