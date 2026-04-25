<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionLog extends Model
{
    protected $connection = 'pgsql';

    protected $fillable = [
        'user_id',
        'user_type',
        'institution_id',
        'group_id',
        'started_at',
        'ended_at',
        'duration_minutes',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function scopeStudents(Builder $query): void
    {
        $query->where('user_type', 'student');
    }

    public function scopeTeachers(Builder $query): void
    {
        $query->where('user_type', 'teacher');
    }

    public function scopeCompleted(Builder $query): void
    {
        $query->whereNotNull('ended_at');
    }

    public function scopePeriod(Builder $query, int $days): void
    {
        $query->where('started_at', '>=', now()->subDays($days));
    }

    public function scopeForInstitution(Builder $query, int $institutionId): void
    {
        $query->where('institution_id', $institutionId);
    }
}
