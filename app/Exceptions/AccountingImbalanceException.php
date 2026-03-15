<?php
namespace App\Exceptions;

use RuntimeException;

class AccountingImbalanceException extends RuntimeException
{
    public function __construct(string $message = 'El asiento contable no cuadra: los débitos deben ser iguales a los créditos.')
    {
        parent::__construct($message);
    }
}
