<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankReconciliationItem extends Model
{
    protected $fillable = [
        'reconciliation_id',
        'source',
        'journal_line_id',
        'date',
        'description',
        'debit',
        'credit',
        'reconciled',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'debit' => 'float',
            'credit' => 'float',
            'reconciled' => 'boolean',
        ];
    }

    public function reconciliation(): BelongsTo
    {
        return $this->belongsTo(BankReconciliation::class);
    }

    public function journalLine(): BelongsTo
    {
        return $this->belongsTo(JournalLine::class);
    }

    /** Monto neto: positivo = débito (ingreso al banco), negativo = crédito (salida). */
    public function netAmount(): float
    {
        return $this->debit - $this->credit;
    }
}
