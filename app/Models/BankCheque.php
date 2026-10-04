<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankCheque extends Model
{
    use HasFactory, BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'type',
        'party_type',
        'party_name',
        'bank_name',
        'cheque_number',
        'cheque_date',
        'clearing_date',
        'amount',
        'status',
        'account_id',
        'note',
        'user_id',
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'cheque_date'   => 'date',
        'clearing_date' => 'date',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}