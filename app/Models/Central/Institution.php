<?php

namespace App\Models\Central;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Institution extends Model
{
    protected $fillable = [
        'name',
        'nit',
        'city',
        'active',
        'coordinator_id',
        'contract_starts_at',
        'contract_expires_at',
        'contract_notified_30d',
        'contract_notified_15d',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'contract_starts_at' => 'date',
            'contract_expires_at' => 'date',
            'contract_notified_30d' => 'boolean',
            'contract_notified_15d' => 'boolean',
        ];
    }

    public function contractStatus(): string
    {
        if (! $this->contract_expires_at) {
            return 'sin_fecha';
        }

        $days = now()->startOfDay()->diffInDays($this->contract_expires_at, false);

        if ($days < 0) {
            return 'vencido';
        }

        if ($days <= 15) {
            return 'critico';
        }

        if ($days <= 30) {
            return 'proximo';
        }

        return 'vigente';
    }

    public function coordinator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }
}
