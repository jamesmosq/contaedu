<?php

namespace App\Services;

use App\Models\Tenant\Account;
use App\Models\Tenant\BankAccount;
use App\Models\Tenant\BankReconciliation;
use App\Models\Tenant\BankReconciliationItem;
use App\Models\Tenant\BankTransaction;
use App\Models\Tenant\JournalLine;
use Illuminate\Support\Collection;

class BankReconciliationService
{
    /**
     * Crea una conciliación y carga los movimientos del libro para el período.
     * Si se vincula una bank_account_id, también carga las bank_transactions como ítems del extracto.
     *
     * @param  array{account_id: int, bank_account_id?: int|null, period_start: string, period_end: string, statement_balance: float, notes?: string}  $data
     */
    public function create(array $data): BankReconciliation
    {
        if ($data['period_start'] > $data['period_end']) {
            throw new \InvalidArgumentException('La fecha de inicio del período no puede ser posterior a la fecha de cierre.');
        }

        $reconciliation = BankReconciliation::create([
            'account_id' => $data['account_id'],
            'bank_account_id' => $data['bank_account_id'] ?? null,
            'period_start' => $data['period_start'],
            'period_end' => $data['period_end'],
            'statement_balance' => round((float) $data['statement_balance'], 2),
            'status' => 'borrador',
            'notes' => $data['notes'] ?? null,
        ]);

        $this->loadBookItems($reconciliation);

        // Dirección 1: cargar bank_transactions no conciliadas como partidas del extracto
        if (! empty($data['bank_account_id'])) {
            $this->loadBankTransactionItems($reconciliation);
        }

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
                $q->where('modo', modoContable())
                    ->whereBetween('date', [
                        $reconciliation->period_start->toDateString(),
                        $reconciliation->period_end->toDateString(),
                    ])
                    ->whereNull('deleted_at');
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

    /**
     * Carga las bank_transactions del período como ítems del extracto bancario.
     * Solo carga las no conciliadas aún.
     */
    public function loadBankTransactionItems(BankReconciliation $reconciliation): void
    {
        if (! $reconciliation->bank_account_id) {
            return;
        }

        $transactions = BankTransaction::where('bank_account_id', $reconciliation->bank_account_id)
            ->whereBetween('fecha_transaccion', [
                $reconciliation->period_start->toDateString(),
                $reconciliation->period_end->toDateString(),
            ])
            ->where('conciliado', false)
            ->get();

        foreach ($transactions as $tx) {
            $esCargo = $tx->esCargo();
            BankReconciliationItem::create([
                'reconciliation_id' => $reconciliation->id,
                'source' => 'banco',
                'journal_line_id' => null,
                'date' => $tx->fecha_transaccion->toDateString(),
                'description' => $tx->descripcion.($tx->gmf > 0 ? ' (+GMF $'.number_format($tx->gmf, 0, ',', '.').')' : ''),
                'debit' => $esCargo ? ($tx->valor + $tx->gmf + $tx->comision) : 0,
                'credit' => $esCargo ? 0 : $tx->valor,
                'reconciled' => false,
            ]);
        }
    }

    /** Marca o desmarca un ítem de libro como cruzado con el extracto. Sincroniza BankTransaction. */
    public function toggleReconciled(BankReconciliationItem $item): void
    {
        $newState = ! $item->reconciled;
        $item->update(['reconciled' => $newState]);

        // Sincronizar conciliado en BankTransaction vía journal_entry_id
        if ($item->journal_line_id) {
            $entryId = JournalLine::where('id', $item->journal_line_id)->value('journal_entry_id');
            if ($entryId) {
                BankTransaction::where('journal_entry_id', $entryId)
                    ->update(['conciliado' => $newState]);
            }
        }
    }

    /**
     * Agrega una partida bancaria (nota crédito banco, cargo bancario, interés, etc.)
     * que aparece en el extracto pero no en los libros.
     * Si la conciliación tiene bank_account_id vinculada, también crea la BankTransaction.
     *
     * @param  array{date: string, description: string, debit: float, credit: float}  $data
     */
    public function addBankItem(BankReconciliation $reconciliation, array $data): BankReconciliationItem
    {
        $debit = round((float) ($data['debit'] ?? 0), 2);
        $credit = round((float) ($data['credit'] ?? 0), 2);

        $item = BankReconciliationItem::create([
            'reconciliation_id' => $reconciliation->id,
            'source' => 'banco',
            'journal_line_id' => null,
            'date' => $data['date'],
            'description' => $data['description'],
            'debit' => $debit,
            'credit' => $credit,
            'reconciled' => true,
        ]);

        // Dirección 2: registrar la nota bancaria en el módulo banco
        if ($reconciliation->bank_account_id) {
            $cuenta = BankAccount::find($reconciliation->bank_account_id);
            if ($cuenta) {
                $esCargo = $debit > 0;
                $valor = $esCargo ? $debit : $credit;

                $cuenta->{$esCargo ? 'decrement' : 'increment'}('saldo', $valor);

                BankTransaction::create([
                    'bank_account_id' => $cuenta->id,
                    'tipo' => $esCargo ? 'nota_debito_banco' : 'nota_credito_banco',
                    'valor' => $valor,
                    'gmf' => 0,
                    'comision' => 0,
                    'saldo_despues' => $cuenta->fresh()->saldo,
                    'descripcion' => 'Nota bancaria conciliación — '.$data['description'],
                    'fecha_transaccion' => $data['date'],
                    'conciliado' => true,
                ]);
            }
        }

        return $item;
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
