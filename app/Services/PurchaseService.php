<?php
namespace App\Services;

use App\Enums\PurchaseInvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\Tenant\PurchaseInvoice;
use App\Models\Tenant\Payment;
use App\Models\Tenant\PaymentItem;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    public function __construct(private AccountingService $accounting) {}

    /**
     * Confirma una factura de compra → estado pendiente + asiento contable.
     */
    public function confirmInvoice(PurchaseInvoice $invoice): PurchaseInvoice
    {
        return DB::transaction(function () use ($invoice) {
            $invoice->load('lines.product', 'third');
            $this->accounting->generatePurchaseEntry($invoice);
            $invoice->update(['status' => PurchaseInvoiceStatus::Pendiente]);
            return $invoice;
        });
    }

    /**
     * Registra un pago a proveedor y genera el asiento.
     * $items = [['purchase_invoice_id' => X, 'amount_applied' => Y], ...]
     */
    public function applyPayment(Payment $payment, array $items): Payment
    {
        return DB::transaction(function () use ($payment, $items) {
            $payment->load('third');
            $this->accounting->generatePaymentEntry($payment);

            foreach ($items as $item) {
                PaymentItem::create([
                    'payment_id'          => $payment->id,
                    'purchase_invoice_id' => $item['purchase_invoice_id'],
                    'amount_applied'      => $item['amount_applied'],
                ]);
                // Si el pago cubre el total de la factura, marcarla como pagada
                $inv = PurchaseInvoice::with('payments')->find($item['purchase_invoice_id']);
                if ($inv && $inv->balance() <= 0.01) {
                    $inv->update(['status' => PurchaseInvoiceStatus::Pagada]);
                }
            }

            $payment->update(['status' => PaymentStatus::Aplicado]);
            return $payment;
        });
    }
}
