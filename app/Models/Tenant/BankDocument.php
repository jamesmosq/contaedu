<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankDocument extends Model
{
    protected $fillable = [
        'bank_account_id',
        'tipo',
        'pdf_path',
        'generado_at',
    ];

    protected function casts(): array
    {
        return [
            'generado_at' => 'datetime',
        ];
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function nombreTipo(): string
    {
        return match ($this->tipo) {
            'certificado'  => 'Certificado bancario',
            'referencia'   => 'Referencia bancaria',
            'paz_y_salvo'  => 'Paz y salvo',
            'extracto'     => 'Extracto bancario',
            default        => $this->tipo,
        };
    }
}
