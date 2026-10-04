<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    use HasFactory, BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'staff_id',
        'month',
        'year',
        'salary_date',
        'basic_salary',
        'allowance',
        'overtime_amount',
        'advance_deduction',
        'penalty_deduction',
        'net_salary',
        'payment_method',
        'payment_status',
        'payslip_no',
        'notes',
    ];

    protected $casts = [
        'salary_date'       => 'date',
        'basic_salary'      => 'decimal:2',
        'allowance'         => 'decimal:2',
        'overtime_amount'   => 'decimal:2',
        'advance_deduction' => 'decimal:2',
        'penalty_deduction' => 'decimal:2',
        'net_salary'        => 'decimal:2',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(StaffProfile::class, 'staff_id');
    }
}