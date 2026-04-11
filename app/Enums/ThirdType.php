<?php

namespace App\Enums;

enum ThirdType: string
{
    case Cliente = 'cliente';
    case Proveedor = 'proveedor';
    case Empleado = 'empleado';
    case Ambos = 'ambos';

    public function label(): string
    {
        return match($this) {
            self::Cliente   => 'Cliente',
            self::Proveedor => 'Proveedor',
            self::Empleado  => 'Empleado',
            self::Ambos     => 'Legado',
        };
    }
}
