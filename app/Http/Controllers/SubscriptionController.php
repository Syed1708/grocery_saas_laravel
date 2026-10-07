<?php

namespace App\Http\Controllers;

use App\Models\License;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function expired()
    {
        $license = License::first();
        return view('subscription.expired', compact('license'));
    }

    public function submitPayment(Request $request)
    {
        $request->validate([
            'trx_id' => 'required|string|min:6|max:30',
        ]);

        $license = License::first();
        if ($license) {
            $license->update([
                'last_trx_id' => strtoupper(trim($request->trx_id)),
            ]);
        }

        return back()->with('success', 'আপনার পেমেন্ট ট্রানজেকশন আইডি (TrxID) গ্রহণ করা হয়েছে। ভেরিফিকেশনের পর সিস্টেমটি চালু হয়ে যাবে।');
    }

    // Admin manually adds days / renews subscription
    public function manualRenew(Request $request)
    {
        $request->validate([
            'months' => 'required|integer|min:1|max:12',
        ]);

        if (!auth()->user()->isVendor()) {
            abort(403, 'Unauthorized action.');
        }

        $license = License::first();
        if ($license) {
            $currentExpiry = $license->expires_at->isPast() ? now() : $license->expires_at;
            $newExpiry = $currentExpiry->addMonths((int)$request->months);

            $license->update([
                'expires_at' => $newExpiry,
                'status'     => 'active',
            ]);
        }

        return back()->with('success', 'সাবস্ক্রিপশনের মেয়াদ সফলভাবে বৃদ্ধি করা হয়েছে!');
    }
}