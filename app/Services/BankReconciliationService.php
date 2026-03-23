<?php

namespace App\Services;

use App\Models\Tenant\Account;
use App\Models\Tenant\BankReconciliation;
use App\Models\Tenant\BankReconciliationItem;
use App\Models\Tenant\JournalLine;
use Illuminate\Support\Collection;

class BankReconciliationService
{
    /**
     * Crea una conciliación y carga los movimientos del libro para el período.
     *
     * @param  array{account_id: int, period_start: string, period_end: string, statement_balance: float, notes?: string}  $data
     */
    public function create(array $data): BankReconciliation
    {
        $reconciliation = BankReconciliation::create([
            'account_id' => $data['account_id'],
            'period_start' => $data['period_start'],
            'period_end' => $data['period_end'],
            'statement_balance' => round((float) $data['statement_balance'], 2),
            'status' => 'borrador',
            'notes' => $data['notes'] ?? null,
        ]);

        $this->loadBookItems($reconciliation);

        return $reconciliation->load('items');
    }

    /**
     * Carga (o recarga) los movimientos del libro diario en la conciliación.
     * Omite las líneas que ya existen en la conciliación (por journal_line_id).
     */
    public function loadBookItems(BankReconciliation $reconciliation): void
    {
        $existingLineIds = $reconciliation->items()
            ->whereNotNull('journal_line_id')
            ->pluck('journal_line_id')
            ->toArray();

        $lines = JournalLine::query()
            ->where('account_id', $reconciliation->account_id)
            ->whereHas('journalEntry', function ($q) use ($reconciliation) {
                $q->whereBetween('date', [
                    $reconciliation->period_start->toDateString(),
                    $reconciliation->period_end->toDateString(),
                ]);
            })
            ->with('journalEntry')
            ->whereNotIn('id', $existingLineIds)
            ->get();

        foreach ($lines as $line) {
            BankReconciliationItem::create([
                'reconciliation_id' => $reconciliation->id,
                'source' => 'libro',
                'journal_line_id' => $line->id,
                'date' => $line->journalEntry->date->toDateString(),
                'description' => $line->description ?: $line->journalEntry->description,
                'debit' => $line->debit,
                'credit' => $line->credit,
                'reconciled' => false,
            ]);
        }
    }

    /** Marca o desmarca un ítem de libro como cruzado con el extracto. */
    public function toggleReconciled(BankReconciliationItem $item): void
    {
        $item->update(['reconciled' => ! $item->reconciled]);
    }

    /**
     * Agrega una partida bancaria (nota crédito banco, cargo bancario, interés, etc.)
     * que aparece en el extracto pero no en los libros.
     *
     * @param  array{date: string, description: string, debit: float, credit: float}  $data
     */
    public function addBankItem(BankReconciliation $reconciliation, array $data): BankReconciliationItem
    {
        return BankReconciliationItem::create([
            'reconciliation_id' => $reconciliation->id,
            'source' => 'banco',
            'journal_line_id' => null,
            'date' => $data['date'],
            'description' => $data['description'],
            'debit' => round((float) ($data['debit'] ?? 0), 2),
            'credit' => round((float) ($data['credit'] ?? 0), 2),
            'reconciled' => true,
        ]);
    }

    /** Elimina una partida bancaria agregada manualmente. */
    public function removeBankItem(BankReconciliationItem $item): void
    {
        abort_if($item->source !== 'banco', 403, 'Solo se pueden eliminar partidas de origen banco.');
        $item->delete();
    }

    /** Finaliza la conciliación si está balanceada. */
    public function finalize(BankReconciliation $reconciliation): BankReconciliation
    {
        $reconciliation->load('items');

        abort_if(
            ! $reconciliation->isBalanced(),
            422,
            'La conciliación no está balanceada. Diferencia: $'.number_format(abs($reconciliation->difference()), 2, ',', '.')
        );

        $reconciliation->update(['status' => 'finalizada']);

        return $reconciliation;
    }

    /** Retorna las cuentas bancarias disponibles (1105 Caja, 1110 Bancos y sus hijas). */
    public function bankAccounts(): Collection
    {
        return Account::query()
            ->where('active', true)
            ->where(function ($q) {
                $q->where('code', 'like', '1105%')
                    ->orWhere('code', 'like', '1110%');
            })
            ->orderBy('code')
            ->get();
    }
}
