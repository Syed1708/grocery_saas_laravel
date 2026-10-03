<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    use HasFactory, BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'customer_id',
        'quotation_no',
        'quotation_date',
        'expiry_date',
        'subtotal',
        'discount',
        'grand_total',
        'status',
        'notes',
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'expiry_date'    => 'date',
        'subtotal'       => 'decimal:2',
        'discount'       => 'decimal:2',
        'grand_total'    => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }
}