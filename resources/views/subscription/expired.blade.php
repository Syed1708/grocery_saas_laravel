<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সাবস্ক্রিপশন নবায়ন | Subscription Overdue</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Hind Siliguri', sans-serif; }</style>
</head>
<body class="bg-slate-100 flex items-center justify-center min-h-screen p-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-200">
        
        <!-- Header -->
        <div class="bg-gradient-to-r from-rose-600 to-red-600 p-6 text-center text-white">
            <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h1 class="text-xl font-bold">মাসিক সাবস্ক্রিপশনের মেয়াদ শেষ</h1>
            <p class="text-sm text-red-100 mt-1">{{ $license->client_name ?? 'আপনার শপ' }}</p>
        </div>

        <div class="p-6 space-y-5">
            <!-- Amount Card -->
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 flex justify-between items-center">
                <div>
                    <p class="text-xs text-slate-500 font-semibold uppercase">মাসিক সফটওয়্যার বিল</p>
                    <p class="text-2xl font-bold text-slate-800">৳{{ number_format($license->monthly_fee ?? 1500, 2) }}</p>
                </div>
                <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">বকেয়া</span>
            </div>

            <!-- Payment Methods (bKash & Nagad) -->
            <div class="space-y-3">
                <p class="text-xs font-bold text-slate-600 uppercase tracking-wider">পেমেন্ট করার নিয়ম (Send Money / Payment):</p>
                
                <div class="flex items-center justify-between p-3 rounded-lg bg-pink-50 border border-pink-200 text-pink-900">
                    <div class="flex items-center gap-3">
                        <span class="font-bold text-base">bKash</span>
                        <span class="text-sm font-mono font-semibold">{{ $license->bkash_number ?? '01700000000' }}</span>
                    </div>
                    <span class="text-xs font-semibold bg-pink-200 text-pink-800 px-2 py-0.5 rounded">Personal</span>
                </div>

                <div class="flex items-center justify-between p-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-900">
                    <div class="flex items-center gap-3">
                        <span class="font-bold text-base">Nagad</span>
                        <span class="text-sm font-mono font-semibold">{{ $license->nagad_number ?? '01800000000' }}</span>
                    </div>
                    <span class="text-xs font-semibold bg-amber-200 text-amber-800 px-2 py-0.5 rounded">Personal</span>
                </div>
            </div>

            <!-- TrxID Submission Form -->
            @if(session('success'))
                <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-800 text-xs font-medium text-center">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('subscription.submit-payment') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">পেমেন্ট করার পর TrxID লিখুন:</label>
                    <input type="text" name="trx_id" placeholder="e.g. BL9A7K2M" required 
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-rose-500 focus:outline-none">
                </div>
                <button type="submit" class="w-full py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-lg text-sm transition">
                    পেমেন্ট ভেরিফাই করতে সাবমিট করুন
                </button>
            </form>

            <!-- Direct Support -->
            <div class="pt-3 border-t border-slate-200 flex justify-between items-center text-xs">
                <a href="https://wa.me/{{ $license->support_whatsapp ?? '8801900000000' }}?text=Hello,%20I%20want%20to%20renew%20my%20POS%20subscription" 
                   target="_blank" class="text-emerald-600 hover:underline flex items-center gap-1 font-semibold">
                    💬 WhatsApp এ যোগাযোগ করুন
                </a>
                
                <form action="{{ route('tyro-login.logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-slate-500 hover:text-slate-700">লগআউট</button>
                </form>
            </div>

        </div>
    </div>
</body>
</html>