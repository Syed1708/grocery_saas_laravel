<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashRegister extends Model
{
    use HasFactory, BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'user_id',
        'closing_date',
        'opening_time',
        'closing_time',
        'opening_cash',
        'cash_sales',
        'cash_due_collected',
        'cash_other_income',
        'cash_expenses',
        'cash_supplier_paid',
        'cash_refunds',
        'expected_cash',
        'counted_cash',
        'difference',
        'total_invoices',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'closing_date'       => 'date',
            'opening_cash'       => 'decimal:2',
            'cash_sales'         => 'decimal:2',
            'cash_due_collected' => 'decimal:2',
            'cash_other_income'  => 'decimal:2',
            'cash_expenses'      => 'decimal:2',
            'cash_supplier_paid' => 'decimal:2',
            'cash_refunds'       => 'decimal:2',
            'expected_cash'      => 'decimal:2',
            'counted_cash'       => 'decimal:2',
            'difference'         => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}