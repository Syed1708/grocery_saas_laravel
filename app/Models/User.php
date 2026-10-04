<?php

namespace App\Models;

use App\Traits\BelongsToBranch; // 👈 1. Import Trait
use HasinHayder\Tyro\Concerns\HasTyroRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    // 👇 2. Add BelongsToBranch here
    use HasApiTokens, HasFactory, Notifiable, HasTyroRoles, BelongsToBranch;

    protected $fillable = [
        'branch_id',
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
            'password'          => 'hashed',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin') || $this->hasRole('super-admin') || $this->hasRole('owner');
    }

    public function isManager(): bool
    {
        return $this->hasRole('manager');
    }

    public function isCashier(): bool
    {
        return $this->hasRole('cashier');
    }

    public function allPrivileges()
    {
        return $this->roles->flatMap(function ($role) {
            return $role->privileges ?? collect();
        })->unique('id');
    }

     /**
     * Link user account to their staff profile
     */
    public function staffProfile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(StaffProfile::class, 'user_id');
    }
}