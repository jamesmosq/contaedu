<?php
namespace App\Enums;
enum PurchaseOrderStatus: string {
    case Pendiente  = 'pendiente';
    case Parcial    = 'parcial';
    case Recibida   = 'recibida';
    case Cancelada  = 'cancelada';
    public function label(): string {
        return match($this) {
            self::Pendiente => 'Pendiente',
            self::Parcial   => 'Parcial',
            self::Recibida  => 'Recibida',
            self::Cancelada => 'Cancelada',
        };
    }
    public function color(): string {
        return match($this) {
            self::Pendiente => 'bg-yellow-100 text-yellow-700',
            self::Parcial   => 'bg-blue-100 text-blue-700',
            self::Recibida  => 'bg-green-100 text-green-700',
            self::Cancelada => 'bg-slate-100 text-slate-500',
        };
    }
}
