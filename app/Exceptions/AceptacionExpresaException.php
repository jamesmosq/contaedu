<?php

namespace App\Exceptions;

use RuntimeException;

class AceptacionExpresaException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('No se puede anular esta factura porque el adquirente ya emitió la Aceptación Expresa (evento 033). La operación quedó firme jurídicamente.');
    }
}
