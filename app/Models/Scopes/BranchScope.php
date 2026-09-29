<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class BranchScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // 🛡️ Auth::hasUser() ONLY runs if the user is already resolved in memory!
        // This is 100% recursion-proof and never crashes during login!
        if (Auth::hasUser()) {
            $user = Auth::user();

            // 1. Cashier / Staff: strictly locked to their single assigned branch
            if (!$user->isSuperAdmin() && !$user->hasRole('admin') && !$user->hasRole('owner') && $user->branch_id) {
                $builder->where($model->getTable() . '.branch_id', $user->branch_id);
                return;
            }

            // 2. Admin / Owner: filter by active branch in session
            $activeBranchId = session('active_branch_id') ?? $user->branch_id;

            if ($activeBranchId && $activeBranchId !== 'all') {
                // If querying Users: show this branch's staff + Store Owners (whose branch_id is null)
                if ($model instanceof \App\Models\User) {
                    $builder->where(function ($query) use ($model, $activeBranchId) {
                        $query->where($model->getTable() . '.branch_id', $activeBranchId)
                              ->orWhereNull($model->getTable() . '.branch_id');
                    });
                } else {
                    // For Products, Sales, Expenses: strictly this branch
                    $builder->where($model->getTable() . '.branch_id', $activeBranchId);
                }
            }
        }
    }
}