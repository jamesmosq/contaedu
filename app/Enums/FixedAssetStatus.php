<?php

namespace App\Enums;

enum FixedAssetStatus: string
{
    case Activo = 'activo';
    case TotalmenteDepreciado = 'totalmente_depreciado';
    case DadoDeBaja = 'dado_de_baja';

    public function label(): string
    {
        return match ($this) {
            self::Activo => 'Activo',
            self::TotalmenteDepreciado => 'Totalmente depreciado',
            self::DadoDeBaja => 'Dado de baja',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Activo => 'green',
            self::TotalmenteDepreciado => 'slate',
            self::DadoDeBaja => 'red',
        };
    }
}
