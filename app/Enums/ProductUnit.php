<?php

namespace App\Enums;

enum ProductUnit: string
{
    case Unidad = 'und';
    case Kilogramo = 'kg';
    case Litro = 'lt';
    case Metro = 'm';
    case Caja = 'caja';
    case Par = 'par';
    case Otro = 'otro';

    public function label(): string
    {
        return match($this) {
            self::Unidad => 'Unidad (und)',
            self::Kilogramo => 'Kilogramo (kg)',
            self::Litro => 'Litro (lt)',
            self::Metro => 'Metro (m)',
            self::Caja => 'Caja',
            self::Par => 'Par',
            self::Otro => 'Otro',
        };
    }
}
