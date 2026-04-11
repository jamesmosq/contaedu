<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankCheck extends Model
{
    protected $fillable = [
        'bank_account_id',
        'numero_cheque',
        'beneficiario',
        'valor',
        'fecha_emision',
        'fecha_cobro',
        'estado',
        'journal_entry_id',
    ];

    protected function casts(): array
    {
        return [
            'valor'         => 'float',
            'fecha_emision' => 'date',
            'fecha_cobro'   => 'date',
        ];
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function estaEmitido(): bool
    {
        return $this->estado === 'emitido';
    }

    public function estaPendiente(): bool
    {
        return $this->estado === 'emitido';
    }

    /** Días que lleva emitido sin cobrar. */
    public function diasPendiente(): int
    {
        if ($this->estado !== 'emitido') {
            return 0;
        }

        return (int) now()->diffInDays($this->fecha_emision);
    }
}
