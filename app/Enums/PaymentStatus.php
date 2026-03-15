<?php
namespace App\Enums;
enum PaymentStatus: string {
    case Borrador = 'borrador';
    case Aplicado = 'aplicado';
    case Anulado  = 'anulado';
    public function label(): string {
        return match($this) {
            self::Borrador => 'Borrador',
            self::Aplicado => 'Aplicado',
            self::Anulado  => 'Anulado',
        };
    }
    public function color(): string {
        return match($this) {
            self::Borrador => 'bg-slate-100 text-slate-600',
            self::Aplicado => 'bg-green-100 text-green-700',
            self::Anulado  => 'bg-red-100 text-red-600',
        };
    }
}
