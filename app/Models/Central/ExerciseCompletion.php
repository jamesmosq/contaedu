<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExerciseCompletion extends Model
{
    protected $connection = 'pgsql';

    protected $fillable = [
        'exercise_id',
        'tenant_id',
        'assignment_id',
        'submitted_at',
        'result',
        'verification_detail',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'verification_detail' => 'array',
        ];
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(ExerciseAssignment::class, 'assignment_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isAprobado(): bool
    {
        return $this->result === 'aprobado';
    }

    public function isParcial(): bool
    {
        return $this->result === 'parcial';
    }
}
