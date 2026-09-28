<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->tenant_id) {
            $tenant = $user->tenant;

            if (!$tenant || $tenant->status !== 'active') {
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'Your shop account is currently inactive or suspended.'
                    ], 403);
                }
                abort(403, 'আপনার দোকানের অ্যাকাউন্টটি সাময়িকভাবে স্থগিত রয়েছে। অ্যাডমিনের সাথে যোগাযোগ করুন।');
            }

            // Share current shop globally with views and container
            app()->instance('currentTenant', $tenant);
            view()->share('currentTenant', $tenant);
        }

        return $next($request);
    }
}