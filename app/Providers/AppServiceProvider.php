<?php

namespace App\Providers;

use HasinHayder\Tyro\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Builder;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    
        // 🛡️ Hide 'super-admin' role from branch staff safely (ZERO recursion)
        Role::addGlobalScope('hide_super_admin_from_staff', function (Builder $builder) {
            // If the user is assigned to a specific branch (branch staff), hide super-admin role
            if (Auth::hasUser() && Auth::user()->branch_id !== null) {
                $builder->where('slug', '!=', 'super-admin');
            }
        });
    
    }
}
