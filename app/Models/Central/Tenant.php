<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'group_id',
        'student_name',
        'company_name',
        'nit_empresa',
        'password',
        'tenancy_db_name',
        'active',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'group_id',
            'student_name',
            'company_name',
            'nit_empresa',
            'password',
            'tenancy_db_name',
            'active',
        ];
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

    public function scores(): HasMany
    {
        return $this->hasMany(StudentScore::class);
    }
}
