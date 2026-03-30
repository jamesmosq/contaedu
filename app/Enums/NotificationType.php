<?php

namespace App\Enums;

enum NotificationType: string
{
    case TransferRequest = 'transfer_request';
    case TransferApproved = 'transfer_approved';
    case TransferRejected = 'transfer_rejected';
    case TransferClaimed = 'transfer_claimed';
    case TransferInfo = 'transfer_info';
    case General = 'general';

    public function label(): string
    {
        return match ($this) {
            self::TransferRequest => 'Solicitud de transferencia',
            self::TransferApproved => 'Transferencia aprobada',
            self::TransferRejected => 'Transferencia rechazada',
            self::TransferClaimed => 'Estudiante reclamado',
            self::TransferInfo => 'Información de transferencia',
            self::General => 'Notificación',
        };
    }

    public function iconColor(): string
    {
        return match ($this) {
            self::TransferRequest => 'text-blue-500',
            self::TransferApproved => 'text-green-500',
            self::TransferRejected => 'text-red-500',
            self::TransferClaimed => 'text-purple-500',
            self::TransferInfo => 'text-slate-500',
            self::General => 'text-gold-500',
        };
    }
}
