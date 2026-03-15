<?php
namespace App\Enums;

enum ReceiptStatus: string
{
    case Borrador = 'borrador';
    case Aplicado = 'aplicado';
    case Anulado  = 'anulado';

    public function label(): string
    {
        return match($this) {
            self::Borrador => 'Borrador',
            self::Aplicado => 'Aplicado',
            self::Anulado  => 'Anulado',
        };
    }
}
