<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            $route = $request->user()->role?->value === 'superadmin'
    ? route('admin.dashboard', absolute: false)
    : route('teacher.dashboard', absolute: false);

return redirect()->intended($route . '?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        $route = $request->user()->role?->value === 'superadmin'
    ? route('admin.dashboard', absolute: false)
    : route('teacher.dashboard', absolute: false);

return redirect()->intended($route . '?verified=1');
    }
}
