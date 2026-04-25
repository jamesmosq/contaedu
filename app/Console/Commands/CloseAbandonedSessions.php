<?php

namespace App\Console\Commands;

use App\Models\Central\SessionLog;
use Illuminate\Console\Command;

class CloseAbandonedSessions extends Command
{
    protected $signature = 'app:close-abandoned-sessions';

    protected $description = 'Cierra sesiones abiertas que llevan más de 8 horas sin logout';

    public function handle(): int
    {
        $cutoff = now()->subHours(8);

        $abandoned = SessionLog::whereNull('ended_at')
            ->where('started_at', '<', $cutoff)
            ->get();

        if ($abandoned->isEmpty()) {
            $this->info('Sin sesiones abandonadas.');

            return self::SUCCESS;
        }

        foreach ($abandoned as $log) {
            $minutes = (int) $log->started_at->diffInMinutes($log->started_at->copy()->addHours(8));

            $log->update([
                'ended_at' => $log->started_at->copy()->addHours(8),
                'duration_minutes' => $minutes,
            ]);
        }

        $this->info("{$abandoned->count()} sesión(es) abandonada(s) cerradas.");

        return self::SUCCESS;
    }
}
