<?php

namespace App\Console\Commands;

use App\Models\Central\Institution;
use App\Models\Central\PlatformNotification;
use Illuminate\Console\Command;

class ExpireInstitutionContracts extends Command
{
    protected $signature = 'app:expire-institution-contracts';

    protected $description = 'Gestiona el ciclo de vida de contratos: activa, desactiva y notifica a coordinadores';

    public function handle(): int
    {
        $today = now()->startOfDay();

        // 1. Activar instituciones cuyo contrato inicia hoy
        $toActivate = Institution::query()
            ->where('active', false)
            ->whereNotNull('contract_starts_at')
            ->whereDate('contract_starts_at', '<=', $today->toDateString())
            ->where(fn ($q) => $q->whereNull('contract_expires_at')
                ->orWhereDate('contract_expires_at', '>=', $today->toDateString()))
            ->get();

        foreach ($toActivate as $inst) {
            $inst->update(['active' => true]);
            $this->line("  Activada: {$inst->name} (inicio {$inst->contract_starts_at->format('d/m/Y')})");
        }

        // 2. Desactivar instituciones cuyo contrato venció
        $toExpire = Institution::query()
            ->where('active', true)
            ->whereNotNull('contract_expires_at')
            ->whereDate('contract_expires_at', '<', $today->toDateString())
            ->get();

        foreach ($toExpire as $inst) {
            $inst->update(['active' => false]);
            $this->line("  Deshabilitada: {$inst->name} (venció {$inst->contract_expires_at->format('d/m/Y')})");

            $this->notifyCoordinator($inst,
                'Contrato vencido',
                "El contrato de {$inst->name} venció el {$inst->contract_expires_at->format('d/m/Y')}. El acceso ha sido deshabilitado automáticamente. Contacta al administrador para renovar."
            );
        }

        // 3. Notificar a coordinadores 30 días antes del vencimiento
        $notify30 = Institution::query()
            ->where('active', true)
            ->where('contract_notified_30d', false)
            ->whereNotNull('contract_expires_at')
            ->whereDate('contract_expires_at', '<=', $today->copy()->addDays(30)->toDateString())
            ->whereDate('contract_expires_at', '>', $today->copy()->addDays(15)->toDateString())
            ->get();

        foreach ($notify30 as $inst) {
            $days = $today->diffInDays($inst->contract_expires_at);
            $this->notifyCoordinator($inst,
                "Contrato vence en {$days} días",
                "El contrato de {$inst->name} vence el {$inst->contract_expires_at->format('d/m/Y')} ({$days} días). Coordina la renovación con el administrador para evitar interrupciones."
            );
            $inst->update(['contract_notified_30d' => true]);
            $this->line("  Notificación 30d enviada: {$inst->name}");
        }

        // 4. Notificar a coordinadores 15 días antes del vencimiento
        $notify15 = Institution::query()
            ->where('active', true)
            ->where('contract_notified_15d', false)
            ->whereNotNull('contract_expires_at')
            ->whereDate('contract_expires_at', '<=', $today->copy()->addDays(15)->toDateString())
            ->whereDate('contract_expires_at', '>=', $today->toDateString())
            ->get();

        foreach ($notify15 as $inst) {
            $days = $today->diffInDays($inst->contract_expires_at);
            $this->notifyCoordinator($inst,
                "Contrato vence en {$days} días — URGENTE",
                "AVISO URGENTE: El contrato de {$inst->name} vence en {$days} días ({$inst->contract_expires_at->format('d/m/Y')}). Si no se renueva, el acceso se deshabilitará automáticamente."
            );
            $inst->update(['contract_notified_15d' => true]);
            $this->line("  Notificación 15d enviada: {$inst->name}");
        }

        $total = $toActivate->count() + $toExpire->count() + $notify30->count() + $notify15->count();

        if ($total === 0) {
            $this->info('Sin cambios en contratos hoy.');
        } else {
            $this->info("Ciclo completado: {$toActivate->count()} activadas, {$toExpire->count()} deshabilitadas, ".
                "{$notify30->count()} notificaciones 30d, {$notify15->count()} notificaciones 15d.");
        }

        return self::SUCCESS;
    }

    private function notifyCoordinator(Institution $inst, string $subject, string $body): void
    {
        if (! $inst->coordinator_id) {
            return;
        }

        PlatformNotification::create([
            'from_user_id' => null,
            'to_user_id' => $inst->coordinator_id,
            'type' => 'contrato',
            'subject' => $subject,
            'body' => $body,
            'related_type' => Institution::class,
            'related_id' => $inst->id,
        ]);
    }
}
