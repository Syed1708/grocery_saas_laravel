<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OwnerTransaction extends Model
{
    use HasFactory, BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'type',
        'transaction_date',
        'amount',
        'payment_method',
        'reference',
        'purpose',
        'notes',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount'           => 'decimal:2',
    ];

    public function scopeCapital($query)
    {
        return $query->where('type', 'capital');
    }

    public function scopeDrawing($query)
    {
        return $query->where('type', 'drawing');
    }
}