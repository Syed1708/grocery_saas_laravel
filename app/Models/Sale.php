<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory, BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'customer_id',
        'user_id',
        'invoice_no',
        'sale_date',
        'subtotal',
        'discount',
        'vat_percent',
        'vat_amount',
        'grand_total',
        'paid_amount',
        'due_amount',
        'change_amount',
        'payment_method',
        'payment_status',
        'offline_uuid',
        'is_synced',
        'notes',
    ];

    protected $casts = [
        'sale_date'     => 'date',
        'subtotal'      => 'decimal:2',
        'discount'      => 'decimal:2',
        'vat_percent'   => 'decimal:2',
        'vat_amount'    => 'decimal:2',
        'grand_total'   => 'decimal:2',
        'paid_amount'   => 'decimal:2',
        'due_amount'    => 'decimal:2',
        'change_amount' => 'decimal:2',
        'is_synced'     => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(SaleReturn::class);
    }

    /**
     * Calculate Total Gross Profit on this sale
     */
    public function grossProfit(): float
    {
        $cost = $this->items->sum(fn($it) => $it->purchase_cost * $it->quantity);
        return (float) ($this->subtotal - $this->discount - $cost);
    }
}