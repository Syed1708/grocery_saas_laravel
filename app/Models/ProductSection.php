<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductSection extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'description', 'status'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'section_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}