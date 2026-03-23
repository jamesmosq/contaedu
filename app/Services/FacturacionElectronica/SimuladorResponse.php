<?php

namespace App\Services\FacturacionElectronica;

use Illuminate\Support\Carbon;

class SimuladorResponse
{
    public function __construct(
        public readonly bool $aceptada,
        public readonly string $cufe,
        public readonly string $codigoRespuesta,
        public readonly string $mensaje,
        public readonly Carbon $fechaValidacion,
        public readonly string $xmlResponse,
    ) {}
}
