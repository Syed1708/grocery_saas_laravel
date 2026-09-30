<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyAsset extends Model
{
    use HasFactory, BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'name',
        'asset_code',
        'serial_number',
        'purchase_date',
        'purchase_cost',
        'current_value',
        'condition',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'purchase_cost' => 'decimal:2',
        'current_value' => 'decimal:2',
    ];

        /**
     * Generate the next unique asset code globally (Never collides!)
     */
    public static function generateNextCode(): string
    {
        $maxId = static::withoutGlobalScopes()->max('id') ?? 0;
        return 'AST-' . str_pad($maxId + 1, 4, '0', STR_PAD_LEFT);
    }
}