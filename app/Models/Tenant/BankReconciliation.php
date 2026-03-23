<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankReconciliation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'account_id',
        'period_start',
        'period_end',
        'statement_balance',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'statement_balance' => 'float',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BankReconciliationItem::class, 'reconciliation_id');
    }

    /** Saldo según libros: suma de todos los movimientos del período en la cuenta. */
    public function bookBalance(): float
    {
        return (float) $this->items
            ->where('source', 'libro')
            ->sum(fn (BankReconciliationItem $i) => $i->debit - $i->credit);
    }

    /** Depósitos en tránsito: débitos en libros no cruzados con el extracto. */
    public function depositsInTransit(): float
    {
        return (float) $this->items
            ->where('source', 'libro')
            ->where('reconciled', false)
            ->where('debit', '>', 0)
            ->sum('debit');
    }

    /** Cheques en circulación: créditos en libros no cruzados con el extracto. */
    public function outstandingChecks(): float
    {
        return (float) $this->items
            ->where('source', 'libro')
            ->where('reconciled', false)
            ->where('credit', '>', 0)
            ->sum('credit');
    }

    /** Ajustes banco: partidas del extracto no registradas en libros (cargos, intereses, etc.). */
    public function bankAdjustments(): float
    {
        return (float) $this->items
            ->where('source', 'banco')
            ->sum(fn (BankReconciliationItem $i) => $i->debit - $i->credit);
    }

    /**
     * Saldo ajustado del extracto:
     * extracto + depósitos en tránsito - cheques en circulación + ajustes banco.
     */
    public function adjustedStatementBalance(): float
    {
        return round(
            $this->statement_balance
            + $this->depositsInTransit()
            - $this->outstandingChecks()
            + $this->bankAdjustments(),
            2
        );
    }

    /** Diferencia: saldo libros - saldo ajustado extracto (0 = conciliado). */
    public function difference(): float
    {
        return round($this->bookBalance() - $this->adjustedStatementBalance(), 2);
    }

    public function isBalanced(): bool
    {
        return abs($this->difference()) < 0.01;
    }

    public function isFinalizada(): bool
    {
        return $this->status === 'finalizada';
    }
}
