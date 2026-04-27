<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Models\Central\Group;
use App\Models\Central\Institution;
use App\Notifications\ResetPasswordNotification;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'must_change_password',
        'role',
        'group_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'role' => UserRole::class,
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function teacherGroups(): HasMany
    {
        return $this->hasMany(Group::class, 'teacher_id');
    }

    /** Institución que coordina este usuario (cuando role = coordinator). */
    public function coordinatedInstitution(): HasOne
    {
        return $this->hasOne(Institution::class, 'coordinator_id');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::Superadmin;
    }

    public function isCoordinator(): bool
    {
        return $this->role === UserRole::Coordinator;
    }

    public function isTeacher(): bool
    {
        return $this->role === UserRole::Teacher;
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
