<?php

namespace App\Services;

use App\Exceptions\AccountingImbalanceException;
use App\Models\Tenant\Account;
use App\Models\Tenant\CashReceipt;
use App\Models\Tenant\CreditNote;
use App\Models\Tenant\DebitNote;
use App\Models\Tenant\FixedAsset;
use App\Models\Tenant\Invoice;
use App\Models\Tenant\JournalEntry;
use App\Models\Tenant\JournalLine;
use App\Models\Tenant\Payment;
use App\Models\Tenant\PurchaseInvoice;
use Carbon\Carbon;

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
        $ar = $this->accountId('1305'); // Cuentas por cobrar clientes
        $revenue = $this->accountId('4135'); // Ingresos por ventas
        $vat = $this->accountId('2408'); // IVA por pagar
        $cogs = $this->accountId('6135'); // Costo de ventas
        $inv = $this->accountId('1435'); // Inventario

        // 1. Débito: cuentas por cobrar (total con IVA)
        $lines[] = [
            'account_id' => $ar,
            'debit' => $invoice->total,
            'credit' => 0,
            'description' => 'Cartera cliente '.$invoice->third->name,
        ];

        // 2. Crédito: ingresos
        $lines[] = [
            'account_id' => $revenue,
            'debit' => 0,
            'credit' => $invoice->subtotal,
            'description' => 'Ingresos factura '.$invoice->fullReference(),
        ];

        // 3. Crédito: IVA (si hay)
        if ($invoice->tax_amount > 0) {
            $lines[] = [
                'account_id' => $vat,
                'debit' => 0,
                'credit' => $invoice->tax_amount,
                'description' => 'IVA factura '.$invoice->fullReference(),
            ];
        }

        // 4. Asiento de costo (si algún producto tiene costo)
        $totalCost = $invoice->lines->sum(fn ($line) => ($line->product?->cost_price ?? 0) * $line->qty);
        if ($totalCost > 0 && $cogs && $inv) {
            $lines[] = ['account_id' => $cogs, 'debit' => $totalCost, 'credit' => 0,          'description' => 'Costo de ventas'];
            $lines[] = ['account_id' => $inv,  'debit' => 0,          'credit' => $totalCost, 'description' => 'Salida de inventario'];
        }

        return $this->createEntry([
            'date' => $invoice->date,
            'reference' => $invoice->fullReference(),
            'description' => 'Factura de venta '.$invoice->fullReference().' — '.$invoice->third->name,
            'document_type' => 'invoice',
            'document_id' => $invoice->id,
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

        $reversalLines = $original->lines->map(fn ($l) => [
            'account_id' => $l->account_id,
            'debit' => $l->credit,
            'credit' => $l->debit,
            'description' => 'Reverso: '.$l->description,
        ])->all();

        return $this->createEntry([
            'date' => now()->toDateString(),
            'reference' => 'AN-'.$invoice->fullReference(),
            'description' => 'Anulación factura '.$invoice->fullReference(),
            'document_type' => 'invoice_reversal',
            'document_id' => $invoice->id,
            'auto_generated' => true,
        ], $reversalLines);
    }

    /**
     * Genera el asiento de un recibo de caja.
     */
    public function generateReceiptEntry(CashReceipt $receipt): JournalEntry
    {
        $cash = $this->accountId('1105'); // Caja
        $ar = $this->accountId('1305'); // Cuentas por cobrar

        $lines = [
            ['account_id' => $cash, 'debit' => $receipt->total, 'credit' => 0,               'description' => 'Recibo caja '.$receipt->third->name],
            ['account_id' => $ar,   'debit' => 0,               'credit' => $receipt->total, 'description' => 'Abono cartera '.$receipt->third->name],
        ];

        return $this->createEntry([
            'date' => $receipt->date,
            'reference' => 'RC-'.str_pad($receipt->id, 5, '0', STR_PAD_LEFT),
            'description' => 'Recibo de caja — '.$receipt->third->name,
            'document_type' => 'cash_receipt',
            'document_id' => $receipt->id,
            'auto_generated' => true,
        ], $lines);
    }

    /**
     * Genera el asiento contable de una nota de crédito aplicada.
     * Débito  4135 Ingresos (reduce ingreso)
     * Débito  2408 IVA por pagar (reduce IVA, si aplica)
     * Crédito 1305 Cuentas por cobrar (reduce cartera)
     */
    public function generateCreditNoteEntry(CreditNote $creditNote): JournalEntry
    {
        $revenue = $this->accountId('4135');
        $vat = $this->accountId('2408');
        $ar = $this->accountId('1305');

        $lines = [];

        if ($revenue) {
            $lines[] = [
                'account_id' => $revenue,
                'debit' => $creditNote->subtotal,
                'credit' => 0,
                'description' => 'NC ingresos — '.$creditNote->invoice->fullReference(),
            ];
        }

        if ($creditNote->tax_amount > 0 && $vat) {
            $lines[] = [
                'account_id' => $vat,
                'debit' => $creditNote->tax_amount,
                'credit' => 0,
                'description' => 'NC IVA — '.$creditNote->invoice->fullReference(),
            ];
        }

        if ($ar) {
            $lines[] = [
                'account_id' => $ar,
                'debit' => 0,
                'credit' => $creditNote->total,
                'description' => 'NC cartera — '.$creditNote->invoice->third->name,
            ];
        }

        return $this->createEntry([
            'date' => $creditNote->date,
            'reference' => $creditNote->fullReference(),
            'description' => 'Nota crédito — '.$creditNote->invoice->fullReference().' — '.$creditNote->reason,
            'document_type' => 'credit_note',
            'document_id' => $creditNote->id,
            'auto_generated' => true,
        ], $lines);
    }

    /**
     * Genera el asiento contable de una nota débito emitida.
     * La nota débito aumenta lo que el cliente debe (cartera).
     *
     * Débito  1305 Cuentas por cobrar  (total de la nota)
     * Crédito 4135 Ingresos            (subtotal)
     * Crédito 2408 IVA por pagar       (si tax_amount > 0)
     */
    public function generateDebitNoteEntry(DebitNote $debitNote): JournalEntry
    {
        $ar = $this->accountId('1305'); // Cuentas por cobrar
        $revenue = $this->accountId('4135'); // Ingresos por ventas
        $vat = $this->accountId('2408'); // IVA por pagar

        $lines = [];

        // Débito: aumenta cartera del cliente
        if ($ar) {
            $lines[] = [
                'account_id' => $ar,
                'debit' => $debitNote->amount,
                'credit' => 0,
                'description' => 'ND cartera — '.$debitNote->invoice->third->name,
            ];
        }

        // Crédito: ingresos
        if ($revenue) {
            $lines[] = [
                'account_id' => $revenue,
                'debit' => 0,
                'credit' => $debitNote->subtotal,
                'description' => 'ND ingresos — '.$debitNote->invoice->fullReference(),
            ];
        }

        // Crédito: IVA (si aplica)
        if ($debitNote->tax_amount > 0 && $vat) {
            $lines[] = [
                'account_id' => $vat,
                'debit' => 0,
                'credit' => $debitNote->tax_amount,
                'description' => 'ND IVA — '.$debitNote->invoice->fullReference(),
            ];
        }

        return $this->createEntry([
            'date' => $debitNote->date,
            'reference' => $debitNote->fullReference(),
            'description' => 'Nota débito — '.$debitNote->invoice->fullReference().' — '.$debitNote->reason,
            'document_type' => 'debit_note',
            'document_id' => $debitNote->id,
            'auto_generated' => true,
        ], $lines);
    }

    /**
     * Genera el asiento de reverso (anulación) de una nota débito.
     */
    public function generateDebitNoteReversal(DebitNote $debitNote): JournalEntry
    {
        $original = JournalEntry::where('document_type', 'debit_note')
            ->where('document_id', $debitNote->id)
            ->with('lines')
            ->first();

        if (! $original) {
            // Si no hay asiento (borrador sin confirmar), no hay nada que revertir
            throw new \RuntimeException('No se encontró el asiento original de la nota débito.');
        }

        $reversalLines = $original->lines->map(fn ($l) => [
            'account_id' => $l->account_id,
            'debit' => $l->credit,
            'credit' => $l->debit,
            'description' => 'Reverso: '.$l->description,
        ])->all();

        return $this->createEntry([
            'date' => now()->toDateString(),
            'reference' => 'AN-'.$debitNote->fullReference(),
            'description' => 'Anulación nota débito '.$debitNote->fullReference(),
            'document_type' => 'debit_note_reversal',
            'document_id' => $debitNote->id,
            'auto_generated' => true,
        ], $reversalLines);
    }

    /**
     * Genera el asiento de una factura de compra confirmada.
     *
     * Sin retenciones:
     *   Débito  1435 Inventario
     *   Débito  2408 IVA descontable
     *   Crédito 2205 Proveedores nacionales (total bruto)
     *
     * Con retenciones:
     *   Débito  1435 Inventario
     *   Débito  2408 IVA descontable
     *   Crédito 2205 Proveedores nacionales (total neto a pagar)
     *   Crédito 2365 Retención en la fuente  (si retefte > 0)
     *   Crédito 2367 Retención IVA           (si reteiva > 0)
     *   Crédito 2368 Retención ICA           (si reteica > 0)
     */
    public function generatePurchaseEntry(PurchaseInvoice $invoice): JournalEntry
    {
        $inventory = $this->accountId('1435'); // Inventario
        $vatInput = $this->accountId('2408'); // IVA descontable
        $suppliers = $this->accountId('2205'); // Proveedores nacionales
        $reteFte = $this->accountId('2365'); // Retención en la fuente
        $reteIva = $this->accountId('2367'); // Reteiva
        $reteIca = $this->accountId('2368'); // Reteica

        // Total bruto (subtotal + IVA), antes de retenciones
        $totalBruto = $invoice->subtotal + $invoice->tax_amount;

        // Neto que realmente se le pagará al proveedor (bruto - retenciones)
        $totalRetenciones = (float) ($invoice->total_retenciones ?? 0);
        $totalNeto = round($totalBruto - $totalRetenciones, 2);

        $lines = [];

        // Débito inventario (subtotal sin IVA)
        if ($inventory) {
            $lines[] = [
                'account_id' => $inventory,
                'debit' => $invoice->subtotal,
                'credit' => 0,
                'description' => 'Compra '.($invoice->supplier_invoice_number ?? 'S/N').' — '.$invoice->third->name,
            ];
        }

        // Débito IVA descontable
        if ($invoice->tax_amount > 0 && $vatInput) {
            $lines[] = [
                'account_id' => $vatInput,
                'debit' => $invoice->tax_amount,
                'credit' => 0,
                'description' => 'IVA compra '.($invoice->supplier_invoice_number ?? 'S/N'),
            ];
        }

        // Crédito proveedores — solo el neto a pagar (bruto menos retenciones)
        if ($suppliers) {
            $lines[] = [
                'account_id' => $suppliers,
                'debit' => 0,
                'credit' => $totalNeto,
                'description' => 'CxP proveedor '.$invoice->third->name,
            ];
        }

        // Crédito retención en la fuente
        if (($invoice->retefte_valor ?? 0) > 0 && $reteFte) {
            $lines[] = [
                'account_id' => $reteFte,
                'debit' => 0,
                'credit' => $invoice->retefte_valor,
                'description' => 'RteFte '.$invoice->retencion_concepto?->label().' — '.$invoice->third->name,
            ];
        }

        // Crédito reteiva
        if (($invoice->reteiva_valor ?? 0) > 0 && $reteIva) {
            $lines[] = [
                'account_id' => $reteIva,
                'debit' => 0,
                'credit' => $invoice->reteiva_valor,
                'description' => 'Reteiva — '.$invoice->third->name,
            ];
        }

        // Crédito reteica
        if (($invoice->reteica_valor ?? 0) > 0 && $reteIca) {
            $lines[] = [
                'account_id' => $reteIca,
                'debit' => 0,
                'credit' => $invoice->reteica_valor,
                'description' => 'Reteica — '.$invoice->third->name,
            ];
        }

        return $this->createEntry([
            'date' => $invoice->date,
            'reference' => $invoice->supplier_invoice_number ?? 'FC-'.str_pad($invoice->id, 5, '0', STR_PAD_LEFT),
            'description' => 'Factura de compra — '.$invoice->third->name,
            'document_type' => 'purchase_invoice',
            'document_id' => $invoice->id,
            'auto_generated' => true,
        ], $lines);
    }

    /**
     * Genera el asiento de una factura de compra DIRECTA (sin OC).
     * Cada línea debita su propia cuenta de gasto/costo (cuenta_gasto_codigo).
     * Si la línea no tiene cuenta asignada usa 5195 (gastos generales).
     *
     * Débito  cuenta_gasto_codigo por ítem   $subtotal_línea
     * Débito  240810 IVA descontable          $iva_total  (si aplica)
     * Crédito 2205 Proveedores                $neto_a_pagar
     * Crédito 2365 RteFte                     (si aplica)
     * Crédito 2367 Reteiva                    (si aplica)
     * Crédito 2368 Reteica                    (si aplica)
     */
    public function generateDirectPurchaseEntry(PurchaseInvoice $invoice): JournalEntry
    {
        $vatInput  = $this->accountId('240810') ?? $this->accountId('2408');
        $suppliers = $this->accountId('2205');
        $reteFte   = $this->accountId('2365');
        $reteIva   = $this->accountId('2367');
        $reteIca   = $this->accountId('2368');

        $totalBruto     = $invoice->subtotal + $invoice->tax_amount;
        $totalRet       = (float) ($invoice->total_retenciones ?? 0);
        $totalNeto      = round($totalBruto - $totalRet, 2);

        $lines = [];

        // Débito por cada línea según su cuenta de gasto
        foreach ($invoice->lines as $line) {
            $codigoCuenta = $line->cuenta_gasto_codigo ?: '5195';
            $accountId    = $this->accountId($codigoCuenta) ?? $this->accountId('5195');

            if ($accountId) {
                $lines[] = [
                    'account_id'  => $accountId,
                    'debit'       => $line->line_subtotal,
                    'credit'      => 0,
                    'description' => $line->description.' — '.($invoice->supplier_invoice_number ?? 'S/N'),
                ];
            }
        }

        // Débito IVA descontable (total IVA de la factura)
        if ($invoice->tax_amount > 0 && $vatInput) {
            $lines[] = [
                'account_id'  => $vatInput,
                'debit'       => $invoice->tax_amount,
                'credit'      => 0,
                'description' => 'IVA compra '.($invoice->supplier_invoice_number ?? 'S/N').' — '.$invoice->third->name,
            ];
        }

        // Crédito proveedores — neto a pagar
        if ($suppliers) {
            $lines[] = [
                'account_id'  => $suppliers,
                'debit'       => 0,
                'credit'      => $totalNeto,
                'description' => 'CxP proveedor '.$invoice->third->name,
            ];
        }

        // Crédito retención en la fuente
        if (($invoice->retefte_valor ?? 0) > 0 && $reteFte) {
            $lines[] = [
                'account_id'  => $reteFte,
                'debit'       => 0,
                'credit'      => $invoice->retefte_valor,
                'description' => 'RteFte — '.$invoice->third->name,
            ];
        }

        // Crédito reteiva
        if (($invoice->reteiva_valor ?? 0) > 0 && $reteIva) {
            $lines[] = [
                'account_id'  => $reteIva,
                'debit'       => 0,
                'credit'      => $invoice->reteiva_valor,
                'description' => 'Reteiva — '.$invoice->third->name,
            ];
        }

        // Crédito reteica
        if (($invoice->reteica_valor ?? 0) > 0 && $reteIca) {
            $lines[] = [
                'account_id'  => $reteIca,
                'debit'       => 0,
                'credit'      => $invoice->reteica_valor,
                'description' => 'Reteica — '.$invoice->third->name,
            ];
        }

        return $this->createEntry([
            'date'          => $invoice->date,
            'reference'     => $invoice->supplier_invoice_number ?? 'FC-'.str_pad($invoice->id, 5, '0', STR_PAD_LEFT),
            'description'   => 'Factura de compra directa — '.$invoice->third->name,
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
    public function generatePaymentEntry(Payment $payment): JournalEntry
    {
        $suppliers = $this->accountId('2205'); // Proveedores nacionales

        // Si el pago viene de una cuenta bancaria → débita Bancos, sino Caja
        $usaBanco    = ! empty($payment->bank_account_id);
        $contrapartida = $usaBanco
            ? $this->accountId('1110') // Bancos
            : $this->accountId('1105'); // Caja
        $contraDesc  = $usaBanco
            ? 'Egreso banco — '.$payment->third->name
            : 'Egreso caja — '.$payment->third->name;

        $lines = [
            ['account_id' => $suppliers,    'debit' => $payment->total, 'credit' => 0,               'description' => 'Pago proveedor '.$payment->third->name],
            ['account_id' => $contrapartida,'debit' => 0,               'credit' => $payment->total, 'description' => $contraDesc],
        ];

        // Líneas adicionales: GMF y comisión ACH si se pagó por banco
        if ($usaBanco) {
            $gmf = \App\Services\BankService::calcularGmf('pago_proveedor', $payment->total);
            if ($gmf > 0) {
                $gmfAccount = $this->accountId('530520') ?? $this->accountId('5305');
                if ($gmfAccount) {
                    $lines[] = ['account_id' => $gmfAccount, 'debit' => $gmf, 'credit' => 0, 'description' => 'GMF 4x1000 pago proveedor'];
                    // El crédito adicional de GMF también sale de Bancos
                    $lines[] = ['account_id' => $contrapartida, 'debit' => 0, 'credit' => $gmf, 'description' => 'GMF débito banco'];
                }
            }
        }

        return $this->createEntry([
            'date' => $payment->date,
            'reference' => 'PAG-'.str_pad($payment->id, 5, '0', STR_PAD_LEFT),
            'description' => 'Pago a proveedor — '.$payment->third->name,
            'document_type' => 'payment',
            'document_id' => $payment->id,
            'auto_generated' => true,
        ], $lines);
    }

    /**
     * Genera el asiento de depreciación mensual de un activo fijo.
     *
     * DR 5160 Gasto depreciación    $cuota
     * CR 1592 Depreciación acumulada $cuota
     */
    public function generateDepreciationEntry(FixedAsset $asset, float $cuota, Carbon $period): JournalEntry
    {
        $gasto = $this->accountId('5160');
        $acumula = $this->accountId('1592');

        $lines = [
            ['account_id' => $gasto,   'debit' => $cuota, 'credit' => 0,     'description' => 'Depreciación '.$asset->name.' '.$period->translatedFormat('F Y')],
            ['account_id' => $acumula, 'debit' => 0,      'credit' => $cuota, 'description' => 'Dep. acum. '.$asset->name],
        ];

        return $this->createEntry([
            'date' => $period->endOfMonth()->toDateString(),
            'reference' => 'DEP-'.str_pad($asset->id, 5, '0', STR_PAD_LEFT).'-'.$period->format('Ym'),
            'description' => 'Depreciación mensual — '.$asset->name.' ('.$period->translatedFormat('F Y').')',
            'document_type' => 'depreciation',
            'document_id' => $asset->id,
            'auto_generated' => true,
        ], $lines);
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    private function accountId(string $code): ?int
    {
        return Account::where('code', $code)->value('id');
    }

    private function createEntry(array $entryData, array $lines): JournalEntry
    {
        $entryData['modo'] = modoContable();

        $totalDebit = array_sum(array_column($lines, 'debit'));
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
