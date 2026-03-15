<?php
namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Models\Tenant\Invoice;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function __construct(private AccountingService $accounting) {}

    /**
     * Crea o actualiza una factura en estado borrador.
     */
    public function saveDraft(array $data, array $lines, ?int $invoiceId = null): Invoice
    {
        return DB::transaction(function () use ($data, $lines, $invoiceId) {
            if ($invoiceId) {
                $invoice = Invoice::findOrFail($invoiceId);
                if (! $invoice->isBorrador()) {
                    throw new \RuntimeException('Solo se pueden editar facturas en borrador.');
                }
                $invoice->update($data);
                $invoice->lines()->delete();
            } else {
                $series        = $data['series'] ?? 'FV';
                $data['number'] = Invoice::nextNumber($series);
                $invoice        = Invoice::create($data);
            }

            foreach ($lines as $line) {
                $invoice->lines()->create($line);
            }

            // Recalcular totales desde las líneas guardadas
            $invoice->load('lines');
            $subtotal  = $invoice->lines->sum('line_subtotal');
            $taxAmount = $invoice->lines->sum('line_tax');
            $invoice->update([
                'subtotal'   => $subtotal,
                'tax_amount' => $taxAmount,
                'total'      => $subtotal + $taxAmount,
            ]);

            return $invoice;
        });
    }

    /**
     * Confirma una factura borrador → emitida y genera el asiento.
     */
    public function confirm(Invoice $invoice): Invoice
    {
        if (! $invoice->isBorrador()) {
            throw new \RuntimeException('Solo se pueden confirmar facturas en borrador.');
        }
        if ($invoice->lines()->count() === 0) {
            throw new \RuntimeException('La factura no tiene líneas.');
        }

        return DB::transaction(function () use ($invoice) {
            $invoice->update(['status' => InvoiceStatus::Emitida]);
            $invoice->load('lines.product', 'third');
            $this->accounting->generateSaleEntry($invoice);
            return $invoice;
        });
    }

    /**
     * Anula una factura emitida y genera el asiento de reverso.
     */
    public function annul(Invoice $invoice): Invoice
    {
        if (! $invoice->isEmitida()) {
            throw new \RuntimeException('Solo se pueden anular facturas emitidas.');
        }

        return DB::transaction(function () use ($invoice) {
            $invoice->load('lines.product', 'third');
            $this->accounting->generateSaleReversal($invoice);
            $invoice->update(['status' => InvoiceStatus::Anulada]);
            return $invoice;
        });
    }
}
