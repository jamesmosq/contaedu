<?php

namespace App\Services\FacturacionElectronica;

use App\Models\Tenant\FeFactura;
use App\Models\Tenant\FeResolucion;

/**
 * Motor de simulación DIAN.
 * Actúa como si fuera el servidor de la DIAN con una tasa de aceptación del 92%.
 * El 8% restante se rechaza con errores realistas para fines pedagógicos.
 */
class DianSimuladorService
{
    public function __construct(
        private readonly XmlUblService $xmlService
    ) {}

    public function validar(FeFactura $factura, FeResolucion $resolucion): SimuladorResponse
    {
        // PASO 1 — Verificar CUFE (96 caracteres hexadecimales)
        if (empty($factura->cufe) || strlen($factura->cufe) !== 96) {
            return $this->rechazar(
                $factura,
                'FAD09',
                'El UUID de la factura no corresponde al algoritmo CUFE-SHA384.',
            );
        }

        // PASO 2 — El adquirente no puede ser el mismo emisor
        if ($factura->num_doc_adquirente === $factura->nit_emisor) {
            return $this->rechazar(
                $factura,
                'FAJ07',
                'El adquirente no puede ser el mismo emisor.',
            );
        }

        // PASO 3 — Error técnico transitorio (3% aleatorio — enseña manejo de errores)
        if (random_int(1, 100) <= 3) {
            return $this->rechazar(
                $factura,
                'ZZZ',
                'Error técnico en transmisión. Intente nuevamente.',
            );
        }

        // PASO 4 — Advertencia si el rango tiene menos de 5 facturas disponibles
        // (no es rechazo, solo se registra en el mensaje)
        $advertencia = '';
        if ($resolucion->rangoDisponible() <= 5) {
            $advertencia = " ADVERTENCIA: Quedan solo {$resolucion->rangoDisponible()} número(s) disponibles en el rango.";
        }

        // ACEPTADO
        $xmlResponse = $this->xmlService->generarApplicationResponse(
            true,
            '00',
            'Documento procesado correctamente.'.$advertencia,
            $factura->cufe,
        );

        return new SimuladorResponse(
            aceptada: true,
            cufe: $factura->cufe,
            codigoRespuesta: '00',
            mensaje: 'Documento procesado correctamente.'.$advertencia,
            fechaValidacion: now(),
            xmlResponse: $xmlResponse,
        );
    }

    private function rechazar(FeFactura $factura, string $codigo, string $mensaje): SimuladorResponse
    {
        $xmlResponse = $this->xmlService->generarApplicationResponse(
            false,
            $codigo,
            $mensaje,
            $factura->cufe ?? '',
        );

        return new SimuladorResponse(
            aceptada: false,
            cufe: $factura->cufe ?? '',
            codigoRespuesta: $codigo,
            mensaje: $mensaje,
            fechaValidacion: now(),
            xmlResponse: $xmlResponse,
        );
    }
}
