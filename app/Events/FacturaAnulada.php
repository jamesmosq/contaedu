<?php

namespace App\Events;

use App\Models\Tenant\FeFactura;
use Illuminate\Foundation\Events\Dispatchable;

class FacturaAnulada
{
    use Dispatchable;

    public function __construct(
        public readonly FeFactura $factura
    ) {}
}
