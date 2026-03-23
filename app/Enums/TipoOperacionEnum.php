<?php

namespace App\Enums;

enum TipoOperacionEnum: string
{
    case Estandar = '10';
    case Mandato = '09';
    case Transporte = '11';
    case Cambiaria = '12';
    case Exportacion = '20';
    case Contingencia = '32';

    public function label(): string
    {
        return match ($this) {
            self::Estandar => 'Estándar',
            self::Mandato => 'Mandato',
            self::Transporte => 'Transporte',
            self::Cambiaria => 'Cambiaria',
            self::Exportacion => 'Exportación',
            self::Contingencia => 'Contingencia',
        };
    }
}
