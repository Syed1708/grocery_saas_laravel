<?php

namespace App\Providers;

use HasinHayder\Tyro\Models\Role;
use HasinHayder\TyroDashboard\Support\DashboardRoute;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\View;

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

            // 🚀 Share $dashboardRoute with ALL views in the application globally
        if (class_exists(DashboardRoute::class)) {
            View::share('dashboardRoute', DashboardRoute::class);
        }
    
        // 🛡️ Hide 'super-admin' role from branch staff safely (ZERO recursion)
        Role::addGlobalScope('hide_super_admin_from_staff', function (Builder $builder) {
            // If the user is assigned to a specific branch (branch staff), hide super-admin role
            if (Auth::hasUser() && Auth::user()->branch_id !== null) {
                $builder->where('slug', '!=', 'super-admin');
            }
        });
    
    }
}
