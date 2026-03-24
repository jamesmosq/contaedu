<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferenceAccessLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'student_tenant_id',
        'demo_tenant_id',
        'accessed_at',
    ];

    protected function casts(): array
    {
        return [
            'accessed_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'student_tenant_id');
    }

    public function demo(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'demo_tenant_id');
    }
}
