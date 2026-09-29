<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    use HasFactory;

    protected $fillable = [
        'license_key',
        'client_name',
        'client_phone',
        'monthly_fee',
        'expires_at',
        'grace_period_days',
        'bkash_number',
        'nagad_number',
        'support_phone',
        'support_whatsapp',
        'status',
        'last_trx_id',
    ];

    protected $casts = [
        'expires_at'  => 'date',
        'monthly_fee' => 'decimal:2',
    ];

    /**
     * Check if subscription has expired (including grace period)
     */
    public function isExpired(): bool
    {
        if ($this->status === 'suspended') {
            return true;
        }

        $graceExpiry = Carbon::parse($this->expires_at)->addDays($this->grace_period_days);
        return now()->startOfDay()->gt($graceExpiry);
    }

    /**
     * Days remaining in current subscription
     */
    public function daysRemaining(): int
    {
        return (int) now()->startOfDay()->diffInDays(Carbon::parse($this->expires_at), false);
    }
}