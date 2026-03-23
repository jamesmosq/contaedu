<?php

namespace App\Enums;

enum TipoDocumentoEnum: string
{
    case NIT = '31';
    case CC = '13';
    case CE = '22';
    case Pasaporte = '91';
    case NitOtro = '42';

    public function label(): string
    {
        return match ($this) {
            self::NIT => 'NIT',
            self::CC => 'Cédula de Ciudadanía',
            self::CE => 'Cédula de Extranjería',
            self::Pasaporte => 'Pasaporte',
            self::NitOtro => 'NIT de otro país',
        };
    }
}
