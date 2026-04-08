<?php

namespace App\Enums;

enum ThirdType: string
{
    case Cliente = 'cliente';
    case Proveedor = 'proveedor';
    case Ambos = 'ambos';

    public function label(): string
    {
        return match($this) {
            self::Cliente => 'Cliente',
            self::Proveedor => 'Proveedor',
            self::Ambos => 'Legado', // conservado solo para datos existentes
        };
    }
}
