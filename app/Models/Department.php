<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'description', 'status'];

    public function staff(): HasMany
    {
        return $this->hasMany(StaffProfile::class, 'department_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}