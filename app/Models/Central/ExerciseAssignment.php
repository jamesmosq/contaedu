<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExerciseAssignment extends Model
{
    protected $connection = 'pgsql';

    protected $fillable = [
        'exercise_id',
        'group_id',
        'assigned_at',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'due_date' => 'date',
        ];
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function completions(): HasMany
    {
        return $this->hasMany(ExerciseCompletion::class, 'assignment_id');
    }
}
