<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountTransaction extends Model
{
    use HasFactory, BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'account_id',
        'type',
        'amount',
        'balance_after',
        'transaction_date',
        'reference_type',
        'reference_id',
        'trx_code',
        'note',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'amount'           => 'decimal:2',
            'balance_after'    => 'decimal:2',
            'transaction_date' => 'date',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}