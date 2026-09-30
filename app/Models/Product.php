<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_id',
        'category_id',
        'brand_id',
        'unit_id',
        'name',
        'name_en',
        'barcode',
        'sku',
        'purchase_price',
        'selling_price',
        'wholesale_price',
        'vat_percent',
        'expiry_date',
        'alert_quantity',
        'image',
        'status',
    ];

    protected $casts = [
        'purchase_price'  => 'decimal:2',
        'selling_price'   => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'vat_percent'     => 'decimal:2',
        'alert_quantity'  => 'decimal:2',
        'expiry_date'     => 'date',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(ProductSection::class, 'section_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(ProductStock::class);
    }

    /**
     * Get real-time stock quantity for the active branch in session
     */
    public function currentStock(): float
    {
        $branchId = session('active_branch_id') ?? Branch::where('is_main', true)->value('id') ?? Branch::first()?->id;
        $stock = $this->stocks()->where('branch_id', $branchId)->first();
        return $stock ? (float) $stock->quantity : 0.00;
    }

    /**
     * Check if product is low on stock in active branch
     */
    public function isLowStock(): bool
    {
        return $this->currentStock() <= (float) $this->alert_quantity;
    }

    /**
     * Calculate Gross Profit Margin %
     */
    public function profitMargin(): float
    {
        if ($this->selling_price > 0) {
            return round((($this->selling_price - $this->purchase_price) / $this->selling_price) * 100, 1);
        }
        return 0.0;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}