<?php

namespace App\Models\Central;

use App\Enums\TransferMode;
use App\Enums\TransferRequestStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferRequest extends Model
{
    protected $fillable = [
        'requesting_user_id',
        'tenant_id',
        'target_group_id',
        'transfer_mode',
        'status',
        'notes',
        'admin_notes',
        'processed_by',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'transfer_mode' => TransferMode::class,
            'status' => TransferRequestStatus::class,
            'processed_at' => 'datetime',
        ];
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requesting_user_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function targetGroup(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'target_group_id');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function isPending(): bool
    {
        return $this->status === TransferRequestStatus::Pending;
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', TransferRequestStatus::Pending->value);
    }

    public function scopeForRequester(Builder $query, int $userId): Builder
    {
        return $query->where('requesting_user_id', $userId);
    }
}
