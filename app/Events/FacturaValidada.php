<?php

namespace App\Events;

use App\Models\Tenant\FeFactura;
use Illuminate\Foundation\Events\Dispatchable;

class FacturaValidada
{
    use Dispatchable;

    public function __construct(
        public readonly FeFactura $factura
    ) {}
}
