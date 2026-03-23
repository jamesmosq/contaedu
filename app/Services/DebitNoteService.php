<?php

namespace App\Services;

use App\Enums\DebitNoteStatus;
use App\Models\Tenant\DebitNote;
use App\Models\Tenant\Invoice;
use Illuminate\Support\Facades\DB;

class DebitNoteService
{
    public function __construct(private AccountingService $accounting) {}

    /**
     * Crea una nota débito en estado borrador.
     *
     * @param  array{date: string, reason: string, subtotal: float, tax_rate: int}  $data
     */
    public function create(Invoice $invoice, array $data): DebitNote
    {
        $subtotal = round((float) $data['subtotal'], 2);
        $taxAmount = round($subtotal * ((int) $data['tax_rate'] / 100), 2);
        $total = $subtotal + $taxAmount;

        return DebitNote::create([
            'invoice_id' => $invoice->id,
            'date' => $data['date'],
            'reason' => $data['reason'],
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'amount' => $total,
            'status' => DebitNoteStatus::Borrador,
        ]);
    }

    /**
     * Confirma la nota débito: genera el asiento contable y cambia estado a Emitida.
     */
    public function confirm(DebitNote $debitNote): DebitNote
    {
        if (! $debitNote->isBorrador()) {
            throw new \RuntimeException('Solo se pueden confirmar notas débito en estado borrador.');
        }

        return DB::transaction(function () use ($debitNote) {
            $debitNote->load('invoice.third');
            $this->accounting->generateDebitNoteEntry($debitNote);
            $debitNote->update(['status' => DebitNoteStatus::Emitida]);

            return $debitNote;
        });
    }

    /**
     * Anula la nota débito: genera asiento de reverso y cambia estado a Anulada.
     */
    public function annul(DebitNote $debitNote): DebitNote
    {
        if (! $debitNote->isEmitida()) {
            throw new \RuntimeException('Solo se pueden anular notas débito emitidas.');
        }

        return DB::transaction(function () use ($debitNote) {
            $this->accounting->generateDebitNoteReversal($debitNote);
            $debitNote->update(['status' => DebitNoteStatus::Anulada]);

            return $debitNote;
        });
    }
}
