<?php

namespace App\Enums;

enum TaxRate: int
{
    case Exento = 0;
    case Reducido = 5;
    case General = 19;

    public function label(): string
    {
        return match($this) {
            self::Exento => 'Exento (0%)',
            self::Reducido => 'Reducido (5%)',
            self::General => 'General (19%)',
        };
    }
}
