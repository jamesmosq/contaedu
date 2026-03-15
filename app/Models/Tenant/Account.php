<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    protected $fillable = [
        'code',
        'name',
        'type',
        'nature',
        'parent_id',
        'level',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'level' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    public function scopeActive($query): mixed
    {
        return $query->where('active', true);
    }

    public function scopeByLevel($query, int $level): mixed
    {
        return $query->where('level', $level);
    }

    public function isAuxiliary(): bool
    {
        return $this->level >= 3;
    }
}
