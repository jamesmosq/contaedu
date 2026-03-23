<?php

namespace App\Models\Tenant;

use App\Enums\DebitNoteStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DebitNote extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_id', 'date', 'reason',
        'subtotal', 'tax_amount', 'amount', 'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'status' => DebitNoteStatus::class,
            'subtotal' => 'float',
            'tax_amount' => 'float',
            'amount' => 'float',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Total de la nota débito (subtotal + IVA).
     * Se usa `amount` como campo de total para mantener compatibilidad con la migración original.
     */
    public function total(): float
    {
        return (float) $this->amount;
    }

    public function fullReference(): string
    {
        return 'ND-'.str_pad($this->id, 5, '0', STR_PAD_LEFT);
    }

    public function isBorrador(): bool
    {
        return $this->status === DebitNoteStatus::Borrador;
    }

    public function isEmitida(): bool
    {
        return $this->status === DebitNoteStatus::Emitida;
    }
}
