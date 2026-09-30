<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'short_code',
        'allow_decimal',
        'base_unit_id',
        'conversion_rate',
        'status',
    ];

    protected $casts = [
        'allow_decimal'   => 'boolean',
        'conversion_rate' => 'decimal:4',
    ];

    public function baseUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    public function subUnits(): HasMany
    {
        return $this->hasMany(Unit::class, 'base_unit_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }



    // In app/Models/Unit.php

    /**
     * Convert any quantity of this unit to its base unit equivalent.
     * e.g. 2 Sacks (rate 50) -> 100 Kg
     * e.g. 500 Grams (rate 0.001) -> 0.5 Kg
     */
    public function toBaseQuantity(float $quantity): float
    {
        if ($this->base_unit_id && $this->conversion_rate) {
            return (float) ($quantity * $this->conversion_rate);
        }
        return (float) $quantity;
    }

    /**
     * Convert a base unit quantity to this sub-unit equivalent.
     * e.g. 100 Kg to Sacks (rate 50) -> 2 Sacks
     */
    public function fromBaseQuantity(float $baseQuantity): float
    {
        if ($this->base_unit_id && $this->conversion_rate && $this->conversion_rate > 0) {
            return (float) ($baseQuantity / $this->conversion_rate);
        }
        return (float) $baseQuantity;
    }
}