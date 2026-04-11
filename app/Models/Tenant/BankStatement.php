<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankStatement extends Model
{
    protected $fillable = [
        'bank_account_id',
        'periodo_inicio',
        'periodo_fin',
        'saldo_inicial',
        'total_debitos',
        'total_creditos',
        'saldo_final',
        'pdf_path',
    ];

    protected function casts(): array
    {
        return [
            'periodo_inicio'  => 'date',
            'periodo_fin'     => 'date',
            'saldo_inicial'   => 'float',
            'total_debitos'   => 'float',
            'total_creditos'  => 'float',
            'saldo_final'     => 'float',
        ];
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    /** Transacciones del período de este extracto. */
    public function transacciones()
    {
        return $this->bankAccount
            ->transactions()
            ->whereBetween('fecha_transaccion', [
                $this->periodo_inicio,
                $this->periodo_fin,
            ])
            ->orderBy('fecha_transaccion')
            ->get();
    }
}
