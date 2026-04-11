<?php

namespace App\Models\Tenant;

use App\Enums\DebitNoteStatus;
use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'modo', 'type', 'series', 'number', 'date', 'due_date',
        'third_id', 'status', 'subtotal', 'tax_amount', 'total', 'notes',
    ];

    public function scopeModoActual($query): void
    {
        $query->where('modo', modoContable());
    }

    protected $casts = [
        'date' => 'date',
        'due_date' => 'date',
        'type' => InvoiceType::class,
        'status' => InvoiceStatus::class,
        'subtotal' => 'float',
        'tax_amount' => 'float',
        'total' => 'float',
    ];

    public function third(): BelongsTo
    {
        return $this->belongsTo(Third::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class);
    }

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class, 'document_id')->where('document_type', 'invoice');
    }

    public function cashReceiptItems(): HasMany
    {
        return $this->hasMany(CashReceiptItem::class);
    }

    public function creditNotes(): HasMany
    {
        return $this->hasMany(CreditNote::class);
    }

    public function debitNotes(): HasMany
    {
        return $this->hasMany(DebitNote::class);
    }

    public function amountReceived(): float
    {
        return (float) $this->cashReceiptItems()->sum('amount_applied');
    }

    public function amountCredited(): float
    {
        return (float) $this->creditNotes()->whereNot('status', 'anulada')->sum('total');
    }

    /**
     * Suma de notas débito emitidas (aumentan lo que el cliente debe).
     */
    public function amountDebited(): float
    {
        return (float) $this->debitNotes()->where('status', DebitNoteStatus::Emitida->value)->sum('amount');
    }

    public function balance(): float
    {
        return max(0, $this->total + $this->amountDebited() - $this->amountReceived() - $this->amountCredited());
    }

    public function isBorrador(): bool
    {
        return $this->status === InvoiceStatus::Borrador;
    }

    public function isEmitida(): bool
    {
        return $this->status === InvoiceStatus::Emitida;
    }

    public function fullReference(): string
    {
        return $this->series.str_pad($this->number, 5, '0', STR_PAD_LEFT);
    }

    public static function nextNumber(string $series): int
    {
        return (int) (static::where('series', $series)->max('number') ?? 0) + 1;
    }
}
