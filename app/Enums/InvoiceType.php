<?php
namespace App\Enums;

enum InvoiceType: string
{
    case Venta  = 'venta';
    case Compra = 'compra';

    public function label(): string
    {
        return match($this) {
            self::Venta  => 'Venta',
            self::Compra => 'Compra',
        };
    }
}
