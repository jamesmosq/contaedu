<?php

namespace App\Services\FacturacionElectronica;

use App\Models\Tenant\FeFactura;

/**
 * Valida la factura electrónica ANTES de enviarla al simulador DIAN.
 * Implementa las 10 reglas de validación basadas en Resolución 000042/2020.
 */
class ValidadorFacturaService
{
    public function validar(FeFactura $factura): ValidationResult
    {
        $errores = [];

        // REGLA 1 — Resolución vigente
        $resolucion = $factura->resolucion;
        if (! $resolucion->estaVigente()) {
            $errores[] = "La resolución {$resolucion->numero_resolucion} venció el {$resolucion->fecha_hasta->format('d/m/Y')}.";
        }

        // REGLA 2 — Consecutivo dentro del rango
        if ($factura->numero && (
            $factura->numero < $resolucion->numero_desde ||
            $factura->numero > $resolucion->numero_hasta
        )) {
            $errores[] = "Consecutivo {$factura->numero} fuera del rango autorizado [{$resolucion->numero_desde}-{$resolucion->numero_hasta}].";
        }

        // REGLA 3 — NIT emisor válido (módulo 11 DIAN)
        if (! empty($factura->nit_emisor) && ! $this->validarNit($factura->nit_emisor, $factura->dv_emisor)) {
            $errores[] = 'El NIT del emisor no es válido (dígito de verificación incorrecto).';
        }

        // REGLA 4 — Identificación del adquirente
        if (empty($factura->num_doc_adquirente)) {
            $errores[] = 'La identificación del adquirente es requerida.';
        } elseif ($factura->tipo_doc_adquirente === '31') {
            // Si es NIT, validar módulo 11
            if (! $this->validarDigitoVerificacion($factura->num_doc_adquirente)) {
                $errores[] = 'El NIT del adquirente no es válido (dígito de verificación incorrecto).';
            }
        }

        // REGLA 5 — Al menos un detalle
        $detalles = $factura->detalles;
        if ($detalles->isEmpty()) {
            $errores[] = 'La factura debe tener al menos un ítem.';
        }

        // REGLA 6 — Total cuadrado
        $sumaLineas = $detalles->sum('total_linea');
        if (abs((float) $factura->total - $sumaLineas) > 0.01) {
            $errores[] = sprintf(
                'El total declarado (%s) no coincide con la suma de líneas (%s).',
                number_format((float) $factura->total, 2, ',', '.'),
                number_format($sumaLineas, 2, ',', '.')
            );
        }

        // REGLA 7 — IVA coherente por línea
        foreach ($detalles as $detalle) {
            $ivaEsperado = round((float) $detalle->subtotal_linea * (float) $detalle->porcentaje_iva / 100, 2);
            if (abs((float) $detalle->valor_iva - $ivaEsperado) > 0.02) {
                $errores[] = "Error de IVA en la línea {$detalle->orden}: {$detalle->descripcion}.";
            }
        }

        // REGLA 8 — Campos obligatorios del adquirente
        if (empty($factura->nombre_adquirente)) {
            $errores[] = 'El nombre del adquirente es obligatorio para factura electrónica.';
        }
        if (empty($factura->email_adquirente)) {
            $errores[] = 'El email del adquirente es obligatorio para factura electrónica.';
        }

        // REGLA 9 — Total mayor a cero
        if ((float) $factura->total <= 0) {
            $errores[] = 'El valor total de la factura debe ser mayor a cero.';
        }

        // REGLA 10 — Fecha de emisión no futura
        if ($factura->fecha_emision->isFuture()) {
            $errores[] = 'La fecha de emisión no puede ser futura.';
        }

        return new ValidationResult($errores);
    }

    /**
     * Valida NIT con dígito de verificación por algoritmo módulo 11 DIAN.
     * Pesos: 3,7,13,17,19,23,29,37,41,43,47,53,59,67,71
     */
    private function validarNit(string $nit, int $dvDeclarado): bool
    {
        return $this->calcularDv($nit) === $dvDeclarado;
    }

    private function validarDigitoVerificacion(string $nit): bool
    {
        // El último dígito del NIT es el DV
        $soloDigitos = preg_replace('/\D/', '', $nit);
        if (strlen($soloDigitos) < 2) {
            return false;
        }
        $numero = substr($soloDigitos, 0, -1);
        $dvDeclarado = (int) substr($soloDigitos, -1);

        return $this->calcularDv($numero) === $dvDeclarado;
    }

    private function calcularDv(string $nit): int
    {
        $pesos = [3, 7, 13, 17, 19, 23, 29, 37, 41, 43, 47, 53, 59, 67, 71];
        $nit = str_pad($nit, 15, '0', STR_PAD_LEFT);
        $suma = 0;
        for ($i = 0; $i < 15; $i++) {
            $suma += (int) $nit[$i] * $pesos[$i];
        }
        $residuo = $suma % 11;

        return $residuo > 1 ? 11 - $residuo : $residuo;
    }
}
