<?php

namespace App\Http\Middleware;

use App\Models\License;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $license = License::first();

        // If no license record exists or it is expired
        if ($license && $license->isExpired()) {
            // Allow access only to renewal endpoints and logout
            $allowedRoutes = [
                'subscription.expired',
                'subscription.submit-payment',
                'tyro-login.logout',
            ];

            if (!in_array($request->route()?->getName(), $allowedRoutes)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'মাসিক সাবস্ক্রিপশনের মেয়াদ শেষ হয়েছে। দয়া করে নবায়ন করুন।'
                    ], 402);
                }
                return redirect()->route('subscription.expired');
            }
        }

        // Share active license with views
        if ($license) {
            view()->share('currentLicense', $license);
        }

        return $next($request);
    }
}