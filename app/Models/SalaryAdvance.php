<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryAdvance extends Model
{
    use HasFactory, BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'staff_id',
        'advance_date',
        'amount',
        'payment_method',
        'purpose',
        'is_deducted',
        'notes',
    ];

    protected $casts = [
        'advance_date' => 'date',
        'amount'       => 'decimal:2',
        'is_deducted'  => 'boolean',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(StaffProfile::class, 'staff_id');
    }
}