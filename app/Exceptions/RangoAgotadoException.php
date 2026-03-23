<?php

namespace App\Exceptions;

use RuntimeException;

class RangoAgotadoException extends RuntimeException
{
    public function __construct(string $resolucion)
    {
        parent::__construct("El rango de la resolución {$resolucion} está agotado. Solicite una nueva resolución a la DIAN.");
    }
}
