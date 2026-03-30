<?php

namespace App\Enums;

enum TransferMode: string
{
    case Keep = 'keep';
    case Reset = 'reset';
    case Fresh = 'fresh';

    public function label(): string
    {
        return match ($this) {
            self::Keep => 'Conservar todo',
            self::Reset => 'Reiniciar transacciones',
            self::Fresh => 'Desde cero',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Keep => 'Cambia de grupo. Los datos, facturas y contabilidad se conservan intactos.',
            self::Reset => 'Cambia de grupo y borra facturas, compras y asientos. Conserva terceros, productos y PUC.',
            self::Fresh => 'Recrea la empresa completamente desde cero. Se pierden todos los datos anteriores.',
        };
    }

    public function warningLevel(): string
    {
        return match ($this) {
            self::Keep => 'none',
            self::Reset => 'medium',
            self::Fresh => 'high',
        };
    }
}
