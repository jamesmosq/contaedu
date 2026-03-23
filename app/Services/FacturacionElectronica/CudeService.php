<?php

namespace App\Services\FacturacionElectronica;

use App\Models\Tenant\FeNotaCredito;
use App\Models\Tenant\FeResolucion;

/**
 * Calcula el CUDE (Código Único de Documento Electrónico) para notas crédito.
 * Usa la misma lógica SHA-384 que el CUFE pero incluye el CUFE de la factura origen.
 */
class CudeService
{
    public function calcular(FeNotaCredito $nota, FeResolucion $resolucion): string
    {
        $facturaOrigen = $nota->facturaOrigen;

        $cadena = implode('', [
            $nota->numero_completo,
            $nota->fecha_emision->format('Y-m-d'),
            $nota->hora_emision->format('H:i:s'),
            number_format((float) $nota->subtotal, 2, '.', ''),
            '01',
            number_format((float) $nota->valor_iva, 2, '.', ''),
            '04',
            '0.00', // INC — notas crédito no suelen tenerlo
            '03',
            '0.00', // ICA — idem
            number_format((float) $nota->total, 2, '.', ''),
            $facturaOrigen->nit_emisor,
            $facturaOrigen->num_doc_adquirente,
            $resolucion->clave_tecnica,
            $resolucion->ambiente,
            $facturaOrigen->cufe ?? '', // Referencia al CUFE de la factura origen
        ]);

        return hash('sha384', $cadena);
    }
}
