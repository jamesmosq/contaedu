<?php

namespace App\Services;

use App\Models\Central\SessionLog;
use App\Models\Central\Tenant;
use App\Models\User;
use Illuminate\Http\Request;

class SessionTracker
{
    public function startStudent(Tenant $student, Request $request): void
    {
        SessionLog::create([
            'user_id' => $student->id,
            'user_type' => 'student',
            'institution_id' => $student->group?->institution_id,
            'group_id' => $student->group_id,
            'started_at' => now(),
            'ip_address' => $request->ip(),
        ]);
    }

    public function endStudent(string $studentId): void
    {
        $log = SessionLog::where('user_id', $studentId)
            ->where('user_type', 'student')
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();

        if ($log) {
            $this->closeLog($log);
        }
    }

    public function startTeacher(User $teacher, Request $request): void
    {
        if (session('impersonating_admin_id')) {
            return;
        }

        $group = $teacher->teacherGroups()->with('institution')->first();

        SessionLog::create([
            'user_id' => (string) $teacher->id,
            'user_type' => 'teacher',
            'institution_id' => $group?->institution_id,
            'group_id' => $group?->id,
            'started_at' => now(),
            'ip_address' => $request->ip(),
        ]);
    }

    public function endTeacher(int $teacherId): void
    {
        if (session('impersonating_admin_id')) {
            return;
        }

        $log = SessionLog::where('user_id', (string) $teacherId)
            ->where('user_type', 'teacher')
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();

        if ($log) {
            $this->closeLog($log);
        }
    }

    private function closeLog(SessionLog $log): void
    {
        $minutes = (int) $log->started_at->diffInMinutes(now());

        $log->update([
            'ended_at' => now(),
            'duration_minutes' => $minutes,
        ]);
    }
}
