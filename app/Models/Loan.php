<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    use HasFactory, BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'type',
        'loan_no',
        'person_name',
        'phone',
        'amount',
        'total_paid',
        'remaining_amount',
        'loan_date',
        'due_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'total_paid'       => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'loan_date'        => 'date',
        'due_date'         => 'date',
    ];

    public function installments(): HasMany
    {
        return $this->hasMany(LoanInstallment::class);
    }

    public function scopeTaken($query)
    {
        return $query->where('type', 'taken');
    }

    public function scopeGiven($query)
    {
        return $query->where('type', 'given');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}