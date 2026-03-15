<?php
namespace App\Enums;
enum PurchaseInvoiceStatus: string {
    case Borrador  = 'borrador';
    case Pendiente = 'pendiente';
    case Pagada    = 'pagada';
    case Anulada   = 'anulada';
    public function label(): string {
        return match($this) {
            self::Borrador  => 'Borrador',
            self::Pendiente => 'Pendiente',
            self::Pagada    => 'Pagada',
            self::Anulada   => 'Anulada',
        };
    }
    public function color(): string {
        return match($this) {
            self::Borrador  => 'bg-slate-100 text-slate-600',
            self::Pendiente => 'bg-yellow-100 text-yellow-700',
            self::Pagada    => 'bg-green-100 text-green-700',
            self::Anulada   => 'bg-slate-100 text-slate-500',
        };
    }
}
