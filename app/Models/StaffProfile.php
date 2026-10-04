<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StaffProfile extends Model
{
    use HasFactory, BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'user_id',
        'department_id',
        'name',
        'phone',
        'emergency_phone',
        'nid_number',
        'designation',
        'joining_date',
        'basic_salary',
        'daily_allowance',
        'overtime_rate',
        'address',
        'status',
    ];

    protected $casts = [
        'joining_date'    => 'date',
        'basic_salary'    => 'decimal:2',
        'daily_allowance' => 'decimal:2',
        'overtime_rate'   => 'decimal:2',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function advances(): HasMany
    {
        return $this->hasMany(SalaryAdvance::class, 'staff_id');
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class, 'staff_id');
    }

    /**
     * Get pending advance balance (অগ্রিম বেতন বকেয়া)
     */
    public function pendingAdvance(): float
    {
        return (float) $this->advances()->where('is_deducted', false)->sum('amount');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}