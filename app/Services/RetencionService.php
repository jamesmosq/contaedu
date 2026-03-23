<?php

namespace App\Services;

use App\Enums\ConceptoRetencion;
use App\Models\Tenant\PurchaseInvoice;

class RetencionService
{
    /**
     * Calcula las retenciones aplicables a una factura de compra.
     *
     * Reglas colombianas simplificadas (contexto educativo):
     * - RteFte: aplica cuando el subtotal supera la base mínima del concepto.
     * - Reteiva: aplica al 15% del IVA cuando el proveedor es régimen simplificado.
     * - Reteica: aplica un porcentaje variable sobre el subtotal cuando se indica.
     *
     * El tercero (proveedor) debe estar cargado en la relación `third`.
     *
     * @param  PurchaseInvoice  $invoice  Factura con `third` y `lines` cargados
     * @param  ConceptoRetencion|null  $concepto  Concepto de RteFte a aplicar (null = no aplica)
     * @param  bool  $aplicarReteiva  Si se debe retener el 15% del IVA
     * @param  float  $porcentajeReteica  Porcentaje de Reteica (0 = no aplica)
     * @return array{
     *   retencion_concepto: string|null,
     *   retefte_base: float,
     *   retefte_porcentaje: float,
     *   retefte_valor: float,
     *   reteiva_valor: float,
     *   reteica_valor: float,
     *   total_retenciones: float,
     *   total_a_pagar: float,
     * }
     */
    public function calcular(
        PurchaseInvoice $invoice,
        ?ConceptoRetencion $concepto = null,
        bool $aplicarReteiva = false,
        float $porcentajeReteica = 0.0,
    ): array {
        $subtotal = (float) $invoice->subtotal;
        $taxAmount = (float) $invoice->tax_amount;
        $brutoPagar = $subtotal + $taxAmount;

        // ── Retención en la Fuente ──────────────────────────────────────────
        $reteFteBase = 0.0;
        $reteFtePorcentaje = 0.0;
        $reteFteValor = 0.0;
        $conceptoStr = null;

        if ($concepto !== null) {
            // Solo retener si el subtotal supera la base mínima
            if ($subtotal >= $concepto->baseMinima()) {
                $reteFteBase = $subtotal;
                $reteFtePorcentaje = $concepto->porcentaje();
                $reteFteValor = round($reteFteBase * ($reteFtePorcentaje / 100), 2);
                $conceptoStr = $concepto->value;
            }
        }

        // ── Reteiva ─────────────────────────────────────────────────────────
        // 15% del IVA. En Colombia aplica principalmente cuando el comprador
        // es gran contribuyente o agente retenedor de IVA y el proveedor es
        // del régimen simplificado. Para el simulador educativo basta con el
        // checkbox del usuario.
        $reteIvaValor = 0.0;
        if ($aplicarReteiva && $taxAmount > 0) {
            $reteIvaValor = round($taxAmount * 0.15, 2);
        }

        // ── Reteica ─────────────────────────────────────────────────────────
        // Impuesto de Industria y Comercio retenido. El porcentaje lo ingresa
        // el usuario porque varía por municipio y actividad.
        $reteIcaValor = 0.0;
        if ($porcentajeReteica > 0) {
            $reteIcaValor = round($subtotal * ($porcentajeReteica / 100), 2);
        }

        // ── Totales ─────────────────────────────────────────────────────────
        $totalRetenciones = $reteFteValor + $reteIvaValor + $reteIcaValor;
        $totalAPagar = round($brutoPagar - $totalRetenciones, 2);

        return [
            'retencion_concepto' => $conceptoStr,
            'retefte_base' => $reteFteBase,
            'retefte_porcentaje' => $reteFtePorcentaje,
            'retefte_valor' => $reteFteValor,
            'reteiva_valor' => $reteIvaValor,
            'reteica_valor' => $reteIcaValor,
            'total_retenciones' => $totalRetenciones,
            'total_a_pagar' => $totalAPagar,
        ];
    }
}
