<?php

namespace App\Exceptions;

use RuntimeException;

class TransicionEstadoInvalidaException extends RuntimeException
{
    public function __construct(string $estadoActual, string $estadoNuevo)
    {
        parent::__construct("No se puede pasar del estado '{$estadoActual}' a '{$estadoNuevo}'.");
    }
}
