<?php
namespace App\Services;

use App\Exceptions\AccountingImbalanceException;
use App\Models\Tenant\Account;
use App\Models\Tenant\CashReceipt;
use App\Models\Tenant\Invoice;
use App\Models\Tenant\JournalEntry;
use App\Models\Tenant\JournalLine;

class AccountingService
{
    /**
     * Genera el asiento contable de una factura de venta confirmada.
     * Lanza AccountingImbalanceException si débitos ≠ créditos.
     */
    public function generateSaleEntry(Invoice $invoice): JournalEntry
    {
        $lines = [];

        // Cuentas base del PUC colombiano
        $ar      = $this->accountId('1305'); // Cuentas por cobrar clientes
        $revenue = $this->accountId('4135'); // Ingresos por ventas
        $vat     = $this->accountId('2408'); // IVA por pagar
        $cogs    = $this->accountId('6135'); // Costo de ventas
        $inv     = $this->accountId('1435'); // Inventario

        // 1. Débito: cuentas por cobrar (total con IVA)
        $lines[] = [
            'account_id'  => $ar,
            'debit'       => $invoice->total,
            'credit'      => 0,
            'description' => 'Cartera cliente ' . $invoice->third->name,
        ];

        // 2. Crédito: ingresos
        $lines[] = [
            'account_id'  => $revenue,
            'debit'       => 0,
            'credit'      => $invoice->subtotal,
            'description' => 'Ingresos factura ' . $invoice->fullReference(),
        ];

        // 3. Crédito: IVA (si hay)
        if ($invoice->tax_amount > 0) {
            $lines[] = [
                'account_id'  => $vat,
                'debit'       => 0,
                'credit'      => $invoice->tax_amount,
                'description' => 'IVA factura ' . $invoice->fullReference(),
            ];
        }

        // 4. Asiento de costo (si algún producto tiene costo)
        $totalCost = $invoice->lines->sum(fn($line) => ($line->product?->cost_price ?? 0) * $line->qty);
        if ($totalCost > 0 && $cogs && $inv) {
            $lines[] = ['account_id' => $cogs, 'debit' => $totalCost, 'credit' => 0,          'description' => 'Costo de ventas'];
            $lines[] = ['account_id' => $inv,  'debit' => 0,          'credit' => $totalCost, 'description' => 'Salida de inventario'];
        }

        return $this->createEntry([
            'date'           => $invoice->date,
            'reference'      => $invoice->fullReference(),
            'description'    => 'Factura de venta ' . $invoice->fullReference() . ' — ' . $invoice->third->name,
            'document_type'  => 'invoice',
            'document_id'    => $invoice->id,
            'auto_generated' => true,
        ], $lines);
    }

    /**
     * Genera el asiento de reverso (anulación) de una factura.
     */
    public function generateSaleReversal(Invoice $invoice): JournalEntry
    {
        // Tomar las líneas del asiento original y reversar débitos/créditos
        $original = JournalEntry::where('document_type', 'invoice')
            ->where('document_id', $invoice->id)
            ->with('lines')
            ->first();

        if (! $original) {
            return $this->generateSaleEntry($invoice); // fallback
        }

        $reversalLines = $original->lines->map(fn($l) => [
            'account_id'  => $l->account_id,
            'debit'       => $l->credit,
            'credit'      => $l->debit,
            'description' => 'Reverso: ' . $l->description,
        ])->all();

        return $this->createEntry([
            'date'           => now()->toDateString(),
            'reference'      => 'AN-' . $invoice->fullReference(),
            'description'    => 'Anulación factura ' . $invoice->fullReference(),
            'document_type'  => 'invoice_reversal',
            'document_id'    => $invoice->id,
            'auto_generated' => true,
        ], $reversalLines);
    }

    /**
     * Genera el asiento de un recibo de caja.
     */
    public function generateReceiptEntry(CashReceipt $receipt): JournalEntry
    {
        $cash = $this->accountId('1105'); // Caja
        $ar   = $this->accountId('1305'); // Cuentas por cobrar

        $lines = [
            ['account_id' => $cash, 'debit' => $receipt->total, 'credit' => 0,               'description' => 'Recibo caja ' . $receipt->third->name],
            ['account_id' => $ar,   'debit' => 0,               'credit' => $receipt->total, 'description' => 'Abono cartera ' . $receipt->third->name],
        ];

        return $this->createEntry([
            'date'           => $receipt->date,
            'reference'      => 'RC-' . str_pad($receipt->id, 5, '0', STR_PAD_LEFT),
            'description'    => 'Recibo de caja — ' . $receipt->third->name,
            'document_type'  => 'cash_receipt',
            'document_id'    => $receipt->id,
            'auto_generated' => true,
        ], $lines);
    }

    /**
     * Genera el asiento de una factura de compra confirmada.
     * Débito 1435 Inventario + Débito 2408 IVA descontable
     * Crédito 2205 Proveedores nacionales
     */
    public function generatePurchaseEntry(\App\Models\Tenant\PurchaseInvoice $invoice): JournalEntry
    {
        $inventory  = $this->accountId('1435'); // Inventario
        $vatInput   = $this->accountId('2408'); // IVA descontable / por pagar
        $suppliers  = $this->accountId('2205'); // Proveedores nacionales

        $lines = [];

        // Débito inventario (subtotal)
        if ($inventory) {
            $lines[] = ['account_id' => $inventory, 'debit' => $invoice->subtotal, 'credit' => 0, 'description' => 'Compra ' . ($invoice->supplier_invoice_number ?? 'S/N') . ' — ' . $invoice->third->name];
        }

        // Débito IVA descontable
        if ($invoice->tax_amount > 0 && $vatInput) {
            $lines[] = ['account_id' => $vatInput, 'debit' => $invoice->tax_amount, 'credit' => 0, 'description' => 'IVA compra ' . ($invoice->supplier_invoice_number ?? 'S/N')];
        }

        // Crédito proveedores (total)
        if ($suppliers) {
            $lines[] = ['account_id' => $suppliers, 'debit' => 0, 'credit' => $invoice->total, 'description' => 'CxP proveedor ' . $invoice->third->name];
        }

        return $this->createEntry([
            'date'          => $invoice->date,
            'reference'     => $invoice->supplier_invoice_number ?? 'FC-' . str_pad($invoice->id, 5, '0', STR_PAD_LEFT),
            'description'   => 'Factura de compra — ' . $invoice->third->name,
            'document_type' => 'purchase_invoice',
            'document_id'   => $invoice->id,
            'auto_generated'=> true,
        ], $lines);
    }

    /**
     * Genera el asiento de un pago a proveedor.
     * Débito 2205 Proveedores nacionales
     * Crédito 1105 Caja
     */
    public function generatePaymentEntry(\App\Models\Tenant\Payment $payment): JournalEntry
    {
        $suppliers = $this->accountId('2205'); // Proveedores nacionales
        $cash      = $this->accountId('1105'); // Caja

        $lines = [
            ['account_id' => $suppliers, 'debit' => $payment->total, 'credit' => 0,               'description' => 'Pago proveedor ' . $payment->third->name],
            ['account_id' => $cash,      'debit' => 0,               'credit' => $payment->total, 'description' => 'Egreso caja — ' . $payment->third->name],
        ];

        return $this->createEntry([
            'date'          => $payment->date,
            'reference'     => 'PAG-' . str_pad($payment->id, 5, '0', STR_PAD_LEFT),
            'description'   => 'Pago a proveedor — ' . $payment->third->name,
            'document_type' => 'payment',
            'document_id'   => $payment->id,
            'auto_generated'=> true,
        ], $lines);
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    private function accountId(string $code): ?int
    {
        return Account::where('code', $code)->value('id');
    }

    private function createEntry(array $entryData, array $lines): JournalEntry
    {
        $totalDebit  = array_sum(array_column($lines, 'debit'));
        $totalCredit = array_sum(array_column($lines, 'credit'));

        if (abs($totalDebit - $totalCredit) >= 0.01) {
            throw new AccountingImbalanceException(
                "Asiento desequilibrado: débitos={$totalDebit}, créditos={$totalCredit}"
            );
        }

        $entry = JournalEntry::create($entryData);
        foreach ($lines as $line) {
            JournalLine::create(array_merge($line, ['journal_entry_id' => $entry->id]));
        }

        return $entry->load('lines');
    }
}
