<?php

namespace App\Services\FacturacionElectronica;

class ValidationResult
{
    public function __construct(
        public readonly array $errores = []
    ) {}

    public function esValido(): bool
    {
        return empty($this->errores);
    }

    public function primerError(): string
    {
        return $this->errores[0] ?? '';
    }
}
