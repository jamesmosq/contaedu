<?php

namespace App\Services\FacturacionElectronica;

use App\Models\Tenant\FeFactura;
use App\Models\Tenant\FeResolucion;

/**
 * Calcula el CUFE (Código Único de Factura Electrónica) según la
 * Resolución DIAN 000042/2020, Sección 10.1.1 — Algoritmo SHA-384.
 *
 * Cadena de concatenación (SIN separadores):
 * NumFac + FecFac + HorFac + ValFac + CodImp1 + ValImp1 + CodImp2 + ValImp2
 *        + CodImp3 + ValImp3 + ValTot + NitOFE + NumAdq + ClTec + TipoAmb
 */
class CufeService
{
    public function calcular(FeFactura $factura, FeResolucion $resolucion): string
    {
        $cadena = implode('', [
            $factura->numero_completo,
            $factura->fecha_emision->format('Y-m-d'),
            $factura->hora_emision->format('H:i:s'),
            number_format((float) $factura->subtotal, 2, '.', ''),
            '01',
            number_format((float) $factura->valor_iva, 2, '.', ''),
            '04',
            number_format((float) $factura->valor_inc, 2, '.', ''),
            '03',
            number_format((float) $factura->valor_ica, 2, '.', ''),
            number_format((float) $factura->total, 2, '.', ''),
            $factura->nit_emisor,
            $factura->num_doc_adquirente,
            $resolucion->clave_tecnica,
            $resolucion->ambiente,
        ]);

        return hash('sha384', $cadena); // 96 chars hexadecimales en minúscula
    }

    public function validar(string $cufe, FeFactura $factura, FeResolucion $resolucion): bool
    {
        return $this->calcular($factura, $resolucion) === $cufe;
    }
}
