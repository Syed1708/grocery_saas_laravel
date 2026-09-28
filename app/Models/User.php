<?php

namespace App\Models;

use App\Models\Tenant;
use HasinHayder\Tyro\Concerns\HasTyroRoles; // <-- Correct Tyro Trait
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasTyroRoles;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'phone',
        'password',
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
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    // Role checks
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin') || is_null($this->tenant_id);
    }

    public function isOwner(): bool
    {
        return $this->hasRole('owner');
    }

    public function isCashier(): bool
    {
        return $this->hasRole('cashier');
    }
}