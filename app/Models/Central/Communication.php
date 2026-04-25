<?php

namespace App\Models\Central;

use App\Enums\CommunicationAudience;
use App\Enums\NotificationType;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Communication extends Model
{
    protected $fillable = [
        'from_user_id',
        'title',
        'body',
        'type',
        'audience',
        'recipient_count',
        'scheduled_at',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => NotificationType::class,
            'audience' => CommunicationAudience::class,
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function isSent(): bool
    {
        return ! is_null($this->sent_at);
    }
}
