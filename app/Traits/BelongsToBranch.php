<?php

namespace App\Traits;

use App\Models\Branch;
use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait BelongsToBranch
{
    protected static function bootBelongsToBranch(): void
    {
        // 1. Auto-apply branch filter
        static::addGlobalScope(new BranchScope);

        // 2. Auto-assign active branch when creating records
        static::creating(function ($model) {
            if (!$model->branch_id && Auth::hasUser()) {
                $activeBranchId = session('active_branch_id') ?? Auth::user()->branch_id;
                if ($activeBranchId) {
                    $model->branch_id = $activeBranchId;
                }
            }
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}