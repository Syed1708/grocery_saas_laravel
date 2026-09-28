<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'owner_name',
        'phone',
        'email',
        'address',
        'trade_license',
        'logo',
        'currency',
        'currency_symbol',
        'status',
        'trial_ends_at',
        'settings',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'settings'       => 'array',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}