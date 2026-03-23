<?php

namespace App\Enums;

enum TipoImpuestoEnum: string
{
    case IVA = '01';
    case INC = '04';
    case ICA = '03';

    public function label(): string
    {
        return match ($this) {
            self::IVA => 'IVA (Impuesto al Valor Agregado)',
            self::INC => 'INC (Impuesto Nacional al Consumo)',
            self::ICA => 'ICA (Impuesto de Industria y Comercio)',
        };
    }
}
