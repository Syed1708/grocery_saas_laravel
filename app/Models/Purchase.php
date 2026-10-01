<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    use HasFactory, BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'supplier_id',
        'chalan_no',
        'purchase_date',
        'subtotal',
        'total_quantity', // 👈 Added
        'discount',
        'transport_cost',
        'vat_percent',    // 👈 Added
        'vat_amount',     // 👈 Added
        'grand_total',
        'previous_due',   // 👈 Added
        'paid_amount',
        'due_amount',
        'payment_method',
        'payment_status',
        'notes',
    ];

    protected $casts = [
        'purchase_date'  => 'date',
        'subtotal'       => 'decimal:2',
        'total_quantity' => 'decimal:2',
        'discount'       => 'decimal:2',
        'transport_cost' => 'decimal:2',
        'vat_percent'    => 'decimal:2',
        'vat_amount'     => 'decimal:2',
        'grand_total'    => 'decimal:2',
        'previous_due'   => 'decimal:2',
        'paid_amount'    => 'decimal:2',
        'due_amount'     => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }
}