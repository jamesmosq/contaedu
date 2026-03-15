<?php
namespace App\Enums;

enum InvoiceStatus: string
{
    case Borrador = 'borrador';
    case Emitida  = 'emitida';
    case Anulada  = 'anulada';

    public function label(): string
    {
        return match($this) {
            self::Borrador => 'Borrador',
            self::Emitida  => 'Emitida',
            self::Anulada  => 'Anulada',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Borrador => 'bg-slate-100 text-slate-600',
            self::Emitida  => 'bg-green-100 text-green-700',
            self::Anulada  => 'bg-red-100 text-red-600',
        };
    }
}
