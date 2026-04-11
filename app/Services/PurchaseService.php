<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Enums\PurchaseInvoiceStatus;
use App\Models\Tenant\Payment;
use App\Models\Tenant\PaymentItem;
use App\Models\Tenant\PurchaseInvoice;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    public function __construct(private AccountingService $accounting) {}

    /**
     * Confirma una factura de compra → estado pendiente + asiento contable.
     *
     * Si se pasan retenciones calculadas, se aplican al modelo antes de generar
     * el asiento. El campo `total` de la factura se recalcula como:
     *   total = subtotal + tax_amount - total_retenciones
     *
     * @param  array{
     *   retencion_concepto: string|null,
     *   retefte_base: float,
     *   retefte_porcentaje: float,
     *   retefte_valor: float,
     *   reteiva_valor: float,
     *   reteica_valor: float,
     *   total_retenciones: float,
     *   total_a_pagar: float,
     * }|null  $retenciones  Resultado de RetencionService::calcular()
     */
    public function confirmInvoice(PurchaseInvoice $invoice, ?array $retenciones = null): PurchaseInvoice
    {
        return DB::transaction(function () use ($invoice, $retenciones) {
            $invoice->load('lines.product', 'third');

            // Aplicar retenciones si se calcularon
            if ($retenciones !== null && $retenciones['total_retenciones'] > 0) {
                $invoice->update([
                    'retencion_concepto' => $retenciones['retencion_concepto'],
                    'retefte_base' => $retenciones['retefte_base'],
                    'retefte_porcentaje' => $retenciones['retefte_porcentaje'],
                    'retefte_valor' => $retenciones['retefte_valor'],
                    'reteiva_valor' => $retenciones['reteiva_valor'],
                    'reteica_valor' => $retenciones['reteica_valor'],
                    'total_retenciones' => $retenciones['total_retenciones'],
                    // El total que se le adeuda al proveedor es el neto
                    'total' => $retenciones['total_a_pagar'],
                ]);
                $invoice->refresh();
            }

            $this->accounting->generatePurchaseEntry($invoice);
            $invoice->update(['status' => PurchaseInvoiceStatus::Pendiente]);
            StudentActivityService::record();

            return $invoice;
        });
    }

    /**
     * Registra un pago a proveedor y genera el asiento.
     * $items = [['purchase_invoice_id' => X, 'amount_applied' => Y], ...]
     */
    /**
     * Registra un pago a proveedor y genera el asiento.
     * $items = [['purchase_invoice_id' => X, 'amount_applied' => Y], ...]
     * Retorna [$payment, $journalEntry] para que el caller pueda registrar BankTransaction.
     */
    public function applyPayment(Payment $payment, array $items): array
    {
        return DB::transaction(function () use ($payment, $items) {
            $payment->load('third');
            $entry = $this->accounting->generatePaymentEntry($payment);

            foreach ($items as $item) {
                PaymentItem::create([
                    'payment_id' => $payment->id,
                    'purchase_invoice_id' => $item['purchase_invoice_id'],
                    'amount_applied' => $item['amount_applied'],
                ]);
                $inv = PurchaseInvoice::with('payments')->find($item['purchase_invoice_id']);
                if ($inv && $inv->balance() <= 0.01) {
                    $inv->update(['status' => PurchaseInvoiceStatus::Pagada]);
                }
            }

            $payment->update(['status' => PaymentStatus::Aplicado]);

            return [$payment, $entry];
        });
    }
}
