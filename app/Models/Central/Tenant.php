<?php

namespace App\Models\Central;

use App\Enums\StudentActivityStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasInternalKeys;
use Stancl\Tenancy\Events;

class Tenant extends Authenticatable implements TenantWithDatabase
{
    use HasDatabase, HasInternalKeys, Notifiable;

    protected static function booting(): void
    {
        static::created(fn (self $tenant) => event(new Events\TenantCreated($tenant)));
        static::updating(fn (self $tenant) => event(new Events\UpdatingTenant($tenant)));
        static::updated(fn (self $tenant) => event(new Events\TenantUpdated($tenant)));
        static::deleting(fn (self $tenant) => event(new Events\DeletingTenant($tenant)));
        static::deleted(fn (self $tenant) => event(new Events\TenantDeleted($tenant)));
    }

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'type',
        'group_id',
        'teacher_id',
        'student_name',
        'company_name',
        'nit_empresa',
        'password',
        'must_change_password',
        'tenancy_db_name',
        'active',
        'published',
        'sector',
        'last_activity_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'published' => 'boolean',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'last_activity_at' => 'datetime',
        ];
    }

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'type',
            'group_id',
            'teacher_id',
            'student_name',
            'company_name',
            'nit_empresa',
            'password',
            'tenancy_db_name',
            'active',
            'published',
            'sector',
            'last_activity_at',
        ];
    }

    /**
     * Determina el estado de actividad del estudiante basado en:
     * - Si nunca ha tenido actividad → NeverActive
     * - Si la última actividad fue en un año anterior → Inactive (semestre cerrado)
     * - Si la última actividad fue hace más de 120 días en el mismo año → Inactive
     * - De lo contrario → Active
     */
    public function activityStatus(): StudentActivityStatus
    {
        if (is_null($this->last_activity_at)) {
            return StudentActivityStatus::NeverActive;
        }

        if ($this->last_activity_at->year < now()->year) {
            return StudentActivityStatus::Inactive;
        }

        if ($this->last_activity_at->diffInDays(now()) > 120) {
            return StudentActivityStatus::Inactive;
        }

        return StudentActivityStatus::Active;
    }

    /** Indica si el estudiante puede ser reclamado directamente sin aprobación del superadmin. */
    public function isFree(): bool
    {
        return $this->activityStatus()->isFree();
    }

    public function getTenantKeyName(): string
    {
        return 'id';
    }

    public function getTenantKey(): string
    {
        return $this->id;
    }

    public function run(callable $callback): mixed
    {
        $originalTenant = tenancy()->tenant;

        tenancy()->initialize($this);
        $result = $callback($this);

        if ($originalTenant) {
            tenancy()->initialize($originalTenant);
        } else {
            tenancy()->end();
        }

        return $result;
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(StudentScore::class);
    }

    /** Grupos a los que está asignada esta empresa demo. */
    public function assignedGroups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'demo_group', 'demo_tenant_id', 'group_id');
    }

    public function isDemo(): bool
    {
        return $this->type === 'demo';
    }

    public function isStudent(): bool
    {
        return $this->type === 'student';
    }
}
