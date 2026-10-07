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

        // If license exists and is expired
        if ($license && $license->isExpired()) {
            $routeName = $request->route()?->getName() ?? '';

            // 🚀 Always allow Login, Logout, and Subscription endpoints!
            $isAllowed = in_array($routeName, [
                'subscription.expired',
                'subscription.submit-payment',
                'subscription.manual-renew',
                'settings.shopsubscription',
                'login',
                'logout',
            ]) || str_starts_with($routeName, 'tyro-login.') || str_starts_with($routeName, 'login');

            // If a Super Admin / Vendor is logged in, allow them to manage and renew
            $user = $request->user();
            if ($user && ($user->isVendor())) {
                $isAllowed = true;
            }

            if (!$isAllowed) {
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