<?php
namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalEntry extends Model
{
    use SoftDeletes;

    protected $fillable = ['modo', 'date', 'reference', 'description', 'document_type', 'document_id', 'auto_generated'];

    public function scopeModoActual($query): void
    {
        $query->where('modo', modoContable());
    }

    protected $casts = [
        'date'           => 'date',
        'auto_generated' => 'boolean',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class);
    }

    public function totalDebits(): float
    {
        return (float) $this->lines->sum('debit');
    }

    public function totalCredits(): float
    {
        return (float) $this->lines->sum('credit');
    }

    public function isBalanced(): bool
    {
        return abs($this->totalDebits() - $this->totalCredits()) < 0.01;
    }
}
