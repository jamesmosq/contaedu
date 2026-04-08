<?php

namespace App\Listeners;

use App\Models\Central\SecurityLog;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;

class LogSecurityEvent
{
    public function handle(Login|Failed|Logout|Lockout|PasswordReset $event): void
    {
        $guard      = $this->resolveGuard($event);
        $eventName  = $this->resolveEventName($event);
        $userType   = $this->resolveUserType($guard, $event);
        $identifier = $this->resolveIdentifier($guard, $event);
        $ip         = request()->ip();

        SecurityLog::create([
            'event'      => $eventName,
            'user_type'  => $userType,
            'identifier' => $identifier,
            'ip_address' => $ip,
            'user_agent' => request()->userAgent(),
        ]);

        // Detectar actividad sospechosa: 5+ intentos fallidos desde la misma IP en 10 minutos
        if ($event instanceof Failed && $ip) {
            $recentFails = SecurityLog::where('event', 'login_failed')
                ->where('ip_address', $ip)
                ->where('created_at', '>=', now()->subMinutes(10))
                ->count();

            if ($recentFails >= 5) {
                $alreadyFlagged = SecurityLog::where('event', 'actividad_sospechosa')
                    ->where('ip_address', $ip)
                    ->where('created_at', '>=', now()->subMinutes(10))
                    ->exists();

                if (! $alreadyFlagged) {
                    SecurityLog::create([
                        'event'      => 'actividad_sospechosa',
                        'user_type'  => $userType,
                        'identifier' => $identifier,
                        'ip_address' => $ip,
                        'user_agent' => request()->userAgent(),
                        'details'    => ['intentos_fallidos' => $recentFails, 'ventana_minutos' => 10],
                    ]);
                }
            }
        }
    }

    private function resolveGuard(Login|Failed|Logout|Lockout|PasswordReset $event): string
    {
        return $event->guard ?? 'web';
    }

    private function resolveEventName(Login|Failed|Logout|Lockout|PasswordReset $event): string
    {
        return match (true) {
            $event instanceof Login         => 'login_success',
            $event instanceof Failed        => 'login_failed',
            $event instanceof Logout        => 'logout',
            $event instanceof Lockout       => 'bloqueo',
            $event instanceof PasswordReset => 'password_reset',
        };
    }

    private function resolveUserType(string $guard, Login|Failed|Logout|Lockout|PasswordReset $event): string
    {
        if ($guard === 'student') {
            return 'Estudiante';
        }

        $user = match (true) {
            $event instanceof Login         => $event->user,
            $event instanceof Logout        => $event->user,
            $event instanceof PasswordReset => $event->user,
            default                         => null,
        };

        if ($user) {
            $role = $user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role;

            return match ($role) {
                'superadmin'  => 'Superadmin',
                'teacher'     => 'Docente',
                'coordinator' => 'Coordinador',
                default       => ucfirst($role),
            };
        }

        return 'Usuario';
    }

    private function resolveIdentifier(string $guard, Login|Failed|Logout|Lockout|PasswordReset $event): string
    {
        if ($event instanceof Failed) {
            return $event->credentials['email']
                ?? $event->credentials['id']
                ?? '—';
        }

        if ($event instanceof Lockout) {
            return $event->request->input('email')
                ?? $event->request->input('cedula')
                ?? $event->request->ip()
                ?? '—';
        }

        $user = match (true) {
            $event instanceof Login         => $event->user,
            $event instanceof Logout        => $event->user,
            $event instanceof PasswordReset => $event->user,
            default                         => null,
        };

        if (! $user) {
            return '—';
        }

        return $guard === 'student'
            ? ($user->id ?? '—')
            : ($user->email ?? '—');
    }
}
