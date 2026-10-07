<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModulePrivilege
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // If user is not logged in, let the 'auth' middleware handle redirection
        if (!$user) {
            return $next($request);
        }

        // 1. Super-admin, Admin, and Owners have full access to everything
        if ($user->isSuperAdmin() || $user->hasRole('admin') || $user->hasRole('owner')) {
            return $next($request);
        }

        $path = trim($request->path(), '/'); // e.g. "branches", "products/create", etc.

        // 2. Map URL prefixes to your Tyro Privileges (from Module1Seeder)
        // 3. Map URL prefixes to required Tyro Privileges (All 9 Modules)
        $privilegeMap = [
            'branches'           => 'branches.manage',
            'units'              => 'settings.manage',
            'assets'             => 'settings.manage',
            'equity'             => 'settings.manage',
            'settings'           => 'settings.manage',
            'shopsubscription'   => 'settings.managesubscription',
            'products'           => 'products.view',
            'sections'           => 'products.manage',
            'categories'         => 'products.manage',
            'brands'             => 'products.manage',
            'purchases'          => 'purchases.manage',
            'suppliers'          => 'purchases.manage',
            'pos'                => 'pos.access',
            'sales'              => 'sales.view',
            'customers'          => 'pos.due',
            'returns'            => 'sales.return',
            'quotations'         => 'pos.access',
            'expenses'           => 'expenses.manage',
            'expense-categories' => 'expenses.manage',
            'incomes'            => 'expenses.manage',
            'staff'              => 'staff.manage',
            'departments'        => 'staff.manage',
            'payrolls'           => 'payroll.manage',
            'advances'           => 'payroll.manage',
            'accounts'           => 'accounts.manage',
            'transfers'          => 'accounts.manage',
            'cheques'            => 'accounts.manage',
            'loans'              => 'accounts.manage',
            'reports'            => 'reports.view',
            'closings'           => 'reports.view',
        ];

        $requiredPrivilege = null;
        foreach ($privilegeMap as $prefix => $priv) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                $requiredPrivilege = $priv;
                break;
            }
        }

        // 3. If route requires a privilege, check if user possesses it
        if ($requiredPrivilege) {
            $userPrivileges = $user->roles->flatMap(function ($role) {
                return $role->privileges ? $role->privileges->pluck('slug') : collect();
            })->toArray();

            $hasAccess = in_array($requiredPrivilege, $userPrivileges) || 
                         (method_exists($user, 'hasPrivilege') && $user->hasPrivilege($requiredPrivilege));

            if (!$hasAccess) {
                // 🛑 Redirect to Dashboard with Tyro's exact restriction message!
                return redirect()->route('tyro-dashboard.index')
                    ->with('error', 'You do not have permission to access this area.');
            }
        }

        return $next($request);
    }
}