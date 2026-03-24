<?php

namespace App\Models\Central;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    protected $fillable = [
        'institution_id',
        'teacher_id',
        'name',
        'period',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    /** Empresas demo asignadas a este grupo. */
    public function demosAsignados(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'demo_group', 'group_id', 'demo_tenant_id');
    }
}
