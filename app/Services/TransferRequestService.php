<?php

namespace App\Services;

use App\Enums\NotificationType;
use App\Enums\TransferMode;
use App\Enums\TransferRequestStatus;
use App\Models\Central\Group;
use App\Models\Central\PlatformNotification;
use App\Models\Central\Tenant;
use App\Models\Central\TransferRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TransferRequestService
{
    /**
     * Solicitud formal para un estudiante ACTIVO.
     * Requiere aprobación del superadmin.
     *
     * Seguridad:
     * - El grupo destino debe pertenecer al docente o a su institución (coordinador).
     * - No se puede solicitar un estudiante que ya está en ese grupo.
     * - Solo funciona si el estudiante existe y está activo.
     */
    public function request(
        User $requester,
        string $tenantId,
        int $targetGroupId,
        TransferMode $mode,
        ?string $notes = null,
    ): TransferRequest {
        $tenant = Tenant::findOrFail($tenantId);
        $group = Group::findOrFail($targetGroupId);

        // El estudiante no debe estar ya en el grupo destino.
        if ($tenant->group_id === $targetGroupId) {
            throw new \DomainException('El estudiante ya pertenece a ese grupo.');
        }

        // El grupo destino debe ser del solicitante (docente) o de su institución (coordinador).
        $this->authorizeGroupAccess($requester, $group);

        // Solo solicitud formal si está activo.
        if ($tenant->isFree()) {
            throw new \DomainException('El estudiante está libre. Use reclamar directamente.');
        }

        // No puede haber otra solicitud pendiente para el mismo estudiante.
        $alreadyPending = TransferRequest::pending()
            ->where('tenant_id', $tenantId)
            ->exists();

        if ($alreadyPending) {
            throw new \DomainException('Ya existe una solicitud pendiente para este estudiante.');
        }

        return DB::transaction(function () use ($requester, $tenant, $targetGroupId, $mode, $notes, $group) {
            $transferRequest = TransferRequest::create([
                'requesting_user_id' => $requester->id,
                'tenant_id' => $tenant->id,
                'target_group_id' => $targetGroupId,
                'transfer_mode' => $mode->value,
                'status' => TransferRequestStatus::Pending->value,
                'notes' => $notes,
            ]);

            // Notificar a todos los superadmins.
            $this->notifySuperadmins(
                type: NotificationType::TransferRequest,
                subject: "Solicitud de transferencia: {$tenant->student_name}",
                body: "{$requester->name} solicita transferir al estudiante {$tenant->student_name} "
                    ."al grupo {$group->name}. Modo: {$mode->label()}.",
                related: $transferRequest,
            );

            // Copia informativa al coordinador del docente solicitante (si existe).
            $this->notifyCoordinatorOf($requester,
                type: NotificationType::TransferInfo,
                subject: "Solicitud enviada: {$tenant->student_name}",
                body: "{$requester->name} ha enviado una solicitud de transferencia "
                    ."para {$tenant->student_name} al grupo {$group->name}.",
                related: $transferRequest,
            );

            return $transferRequest;
        });
    }

    /**
     * Reclamo directo de un estudiante LIBRE (inactivo o nunca activo).
     * No requiere aprobación del superadmin. Se ejecuta inmediatamente.
     *
     * Seguridad:
     * - Se valida server-side que el estudiante sea realmente libre.
     * - Lock pesimista para evitar condición de carrera.
     * - El grupo destino debe pertenecer al reclamante.
     */
    public function claim(
        User $claimer,
        string $tenantId,
        int $targetGroupId,
        TransferMode $mode,
        ?string $notes = null,
    ): void {
        $group = Group::findOrFail($targetGroupId);
        $this->authorizeGroupAccess($claimer, $group);

        DB::transaction(function () use ($claimer, $tenantId, $targetGroupId, $mode, $notes, $group) {
            // Lock pesimista para evitar dos reclamos simultáneos del mismo estudiante.
            $tenant = Tenant::where('id', $tenantId)->lockForUpdate()->firstOrFail();

            if ($tenant->group_id === $targetGroupId) {
                throw new \DomainException('El estudiante ya pertenece a ese grupo.');
            }

            // Validación server-side del estado: DEBE ser libre.
            if (! $tenant->isFree()) {
                throw new \DomainException(
                    'El estudiante está activo en otra institución. Use la solicitud formal.'
                );
            }

            // Cancelar solicitudes pendientes previas sobre este estudiante.
            TransferRequest::pending()
                ->where('tenant_id', $tenantId)
                ->update([
                    'status' => TransferRequestStatus::Cancelled->value,
                    'admin_notes' => 'Cancelada automáticamente: estudiante reclamado directamente.',
                    'processed_at' => now(),
                ]);

            $originGroup = $tenant->group;

            // Ejecutar la transferencia.
            app(TransferStudentService::class)->transfer($tenantId, $targetGroupId, $mode->value);

            // Registrar en el log de transferencias.
            $record = TransferRequest::create([
                'requesting_user_id' => $claimer->id,
                'tenant_id' => $tenant->id,
                'target_group_id' => $targetGroupId,
                'transfer_mode' => $mode->value,
                'status' => TransferRequestStatus::Approved->value,
                'notes' => $notes,
                'processed_by' => $claimer->id,
                'processed_at' => now(),
            ]);

            // Notificación informativa al superadmin (auditoría).
            $this->notifySuperadmins(
                type: NotificationType::TransferClaimed,
                subject: "Estudiante reclamado: {$tenant->student_name}",
                body: "{$claimer->name} reclamó directamente al estudiante {$tenant->student_name} "
                    ."(cédula: {$tenant->id}) al grupo {$group->name}. Modo: {$mode->label()}.",
                related: $record,
            );

            // Notificación informativa al coordinador del reclamante.
            $this->notifyCoordinatorOf($claimer,
                type: NotificationType::TransferClaimed,
                subject: "Estudiante incorporado: {$tenant->student_name}",
                body: "{$claimer->name} incorporó al estudiante {$tenant->student_name} "
                    ."al grupo {$group->name}.",
                related: $record,
            );

            // Notificación al docente/coordinador de origen (si existe y es diferente).
            if ($originGroup) {
                $originTeacher = $originGroup->teacher;
                if ($originTeacher && $originTeacher->id !== $claimer->id) {
                    $this->notify(
                        recipient: $originTeacher,
                        type: NotificationType::TransferInfo,
                        subject: "Estudiante transferido: {$tenant->student_name}",
                        body: "El estudiante {$tenant->student_name} fue incorporado a otra institución "
                            ."desde el grupo {$originGroup->name}.",
                        related: $record,
                    );

                    // Copia al coordinador de la institución origen.
                    $this->notifyCoordinatorOf($originTeacher,
                        type: NotificationType::TransferInfo,
                        subject: "Estudiante transferido: {$tenant->student_name}",
                        body: "El estudiante {$tenant->student_name} del grupo {$originGroup->name} "
                            .'fue incorporado a otra institución.',
                        related: $record,
                    );
                }
            }
        });
    }

    /**
     * Aprueba una solicitud pendiente y ejecuta la transferencia.
     * Solo el superadmin puede llamar este método.
     */
    public function approve(User $admin, TransferRequest $transferRequest, ?string $adminNotes = null): void
    {
        if (! $transferRequest->isPending()) {
            throw new \DomainException('La solicitud no está pendiente.');
        }

        DB::transaction(function () use ($admin, $transferRequest, $adminNotes) {
            // Lock pesimista sobre el tenant al aprobar.
            $tenant = Tenant::where('id', $transferRequest->tenant_id)->lockForUpdate()->firstOrFail();

            // Si el estudiante ya fue reclamado por otro entre tanto, rechazar esta.
            if ($tenant->group_id === $transferRequest->target_group_id) {
                $transferRequest->update([
                    'status' => TransferRequestStatus::Rejected->value,
                    'admin_notes' => 'El estudiante ya fue transferido a ese grupo.',
                    'processed_by' => $admin->id,
                    'processed_at' => now(),
                ]);

                return;
            }

            app(TransferStudentService::class)->transfer(
                $transferRequest->tenant_id,
                $transferRequest->target_group_id,
                $transferRequest->transfer_mode->value,
            );

            $transferRequest->update([
                'status' => TransferRequestStatus::Approved->value,
                'admin_notes' => $adminNotes,
                'processed_by' => $admin->id,
                'processed_at' => now(),
            ]);

            // Notificar al solicitante.
            $this->notify(
                recipient: $transferRequest->requester,
                type: NotificationType::TransferApproved,
                subject: "Transferencia aprobada: {$tenant->student_name}",
                body: "La solicitud de transferencia para {$tenant->student_name} fue aprobada."
                    .($adminNotes ? " Nota: {$adminNotes}" : ''),
                related: $transferRequest,
            );

            // Copia al coordinador del solicitante.
            $this->notifyCoordinatorOf($transferRequest->requester,
                type: NotificationType::TransferApproved,
                subject: "Transferencia aprobada: {$tenant->student_name}",
                body: "La solicitud enviada por {$transferRequest->requester->name} "
                    ."para {$tenant->student_name} fue aprobada.",
                related: $transferRequest,
            );
        });
    }

    /**
     * Rechaza una solicitud pendiente.
     * Solo el superadmin puede llamar este método.
     */
    public function reject(User $admin, TransferRequest $transferRequest, ?string $adminNotes = null): void
    {
        if (! $transferRequest->isPending()) {
            throw new \DomainException('La solicitud no está pendiente.');
        }

        DB::transaction(function () use ($admin, $transferRequest, $adminNotes) {
            $transferRequest->update([
                'status' => TransferRequestStatus::Rejected->value,
                'admin_notes' => $adminNotes,
                'processed_by' => $admin->id,
                'processed_at' => now(),
            ]);

            $tenant = $transferRequest->tenant;

            $this->notify(
                recipient: $transferRequest->requester,
                type: NotificationType::TransferRejected,
                subject: "Transferencia rechazada: {$tenant->student_name}",
                body: "La solicitud de transferencia para {$tenant->student_name} fue rechazada."
                    .($adminNotes ? " Motivo: {$adminNotes}" : ''),
                related: $transferRequest,
            );

            $this->notifyCoordinatorOf($transferRequest->requester,
                type: NotificationType::TransferRejected,
                subject: "Transferencia rechazada: {$tenant->student_name}",
                body: "La solicitud de {$transferRequest->requester->name} "
                    ."para {$tenant->student_name} fue rechazada.",
                related: $transferRequest,
            );
        });
    }

    // ─── Helpers privados ────────────────────────────────────────────────────

    /**
     * Verifica que el grupo destino pertenezca al usuario:
     * - Docente: el grupo debe ser suyo (teacher_id).
     * - Coordinador: el grupo debe pertenecer a su institución.
     */
    private function authorizeGroupAccess(User $user, Group $group): void
    {
        if ($user->isTeacher() && $group->teacher_id !== $user->id) {
            throw new \DomainException('No tienes permisos sobre ese grupo.');
        }

        if ($user->isCoordinator()) {
            $institution = $user->coordinatedInstitution;

            if (! $institution || $group->institution_id !== $institution->id) {
                throw new \DomainException('Ese grupo no pertenece a tu institución.');
            }
        }
    }

    private function notifySuperadmins(
        NotificationType $type,
        string $subject,
        string $body,
        mixed $related = null,
    ): void {
        $superadmins = User::where('role', 'superadmin')->get();

        foreach ($superadmins as $admin) {
            $this->notify($admin, $type, $subject, $body, $related);
        }
    }

    private function notifyCoordinatorOf(
        User $user,
        NotificationType $type,
        string $subject,
        string $body,
        mixed $related = null,
    ): void {
        // Solo aplica para docentes — busca el coordinador de su institución.
        if (! $user->isTeacher()) {
            return;
        }

        $group = $user->teacherGroups()->with('institution.coordinator')->first();
        $coordinator = $group?->institution?->coordinator ?? null;

        if ($coordinator) {
            $this->notify($coordinator, $type, $subject, $body, $related);
        }
    }

    private function notify(
        User $recipient,
        NotificationType $type,
        string $subject,
        string $body,
        mixed $related = null,
    ): void {
        PlatformNotification::create([
            'to_user_id' => $recipient->id,
            'type' => $type->value,
            'subject' => $subject,
            'body' => $body,
            'related_type' => $related ? get_class($related) : null,
            'related_id' => $related?->id,
        ]);
    }
}
