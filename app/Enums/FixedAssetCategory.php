<?php

namespace App\Enums;

enum FixedAssetCategory: string
{
    case Maquinaria = 'maquinaria';
    case MueblesEquipoOficina = 'muebles_equipo_oficina';
    case EquipoComputo = 'equipo_computo';
    case Transporte = 'transporte';
    case Edificios = 'edificios';
    case Otro = 'otro';

    public function label(): string
    {
        return match ($this) {
            self::Maquinaria => 'Maquinaria y equipo',
            self::MueblesEquipoOficina => 'Muebles y equipo de oficina',
            self::EquipoComputo => 'Equipo de cómputo y comunicación',
            self::Transporte => 'Equipo de transporte',
            self::Edificios => 'Construcciones y edificaciones',
            self::Otro => 'Otro',
        };
    }

    /** Código PUC del activo correspondiente. */
    public function cuentaActivoCodigo(): string
    {
        return match ($this) {
            self::Maquinaria => '1516',
            self::MueblesEquipoOficina => '1524',
            self::EquipoComputo => '1528',
            self::Transporte => '1532',
            self::Edificios => '1520',
            self::Otro => '1524',
        };
    }

    /** Vida útil típica en meses según normativa colombiana (NIC 16 simplificada). */
    public function vidaUtilMesesDefecto(): int
    {
        return match ($this) {
            self::Maquinaria => 120, // 10 años
            self::MueblesEquipoOficina => 120, // 10 años
            self::EquipoComputo => 36,  // 3 años
            self::Transporte => 60,  // 5 años
            self::Edificios => 240, // 20 años
            self::Otro => 60,
        };
    }
}
