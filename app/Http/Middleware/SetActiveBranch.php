<?php

namespace App\Http\Middleware;

use App\Models\Branch;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetActiveBranch
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            // If Cashier/Staff: Force session to their assigned branch
            if (!$user->isSuperAdmin() && !$user->hasRole('owner') && $user->branch_id) {
                session(['active_branch_id' => $user->branch_id]);
            }

            // If session has no branch, default to Main Branch
            if (!session()->has('active_branch_id')) {
                $defaultBranchId = $user->branch_id 
                    ?? Branch::where('is_main', true)->value('id') 
                    ?? Branch::first()?->id;

                if ($defaultBranchId) {
                    session(['active_branch_id' => $defaultBranchId]);
                }
            }

            $currentBranch = Branch::find(session('active_branch_id'));
            view()->share('currentBranch', $currentBranch);
        }

        return $next($request);
    }
}