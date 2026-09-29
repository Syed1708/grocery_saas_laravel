@extends('tyro-dashboard::layouts.app')

@section('title', 'নতুন শাখা যোগ করুন')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold">নতুন শাখা যোগ করুন</h1>
        <a href="{{ route('branches.index') }}" class="text-sm text-slate-500 hover:underline">← ফিরে যান</a>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
        <form action="{{ route('branches.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold mb-1">শাখার নাম <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. ধানমন্ডি শাখা" required class="w-full px-3 py-2 border rounded-lg text-sm">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">শাখা কোড (Code) <span class="text-red-500">*</span></label>
                    <input type="text" name="code" value="{{ old('code', 'BR-0'.(\App\Models\Branch::count() + 1)) }}" required class="w-full px-3 py-2 border rounded-lg text-sm font-mono">
                    @error('code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">ফোন নম্বর</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="017XXXXXXXX" class="w-full px-3 py-2 border rounded-lg text-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">শাখার পূর্ণ ঠিকানা</label>
                <textarea name="address" rows="2" placeholder="দোকান নং, রোড, এলাকা..." class="w-full px-3 py-2 border rounded-lg text-sm">{{ old('address') }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">স্ট্যাটাস</label>
                    <select name="status" class="w-full px-3 py-2 border rounded-lg text-sm">
                        <option value="active">সক্রিয় (Active)</option>
                        <option value="inactive">নিষ্ক্রিয় (Inactive)</option>
                    </select>
                </div>
                <div class="flex items-center gap-2 pt-6">
                    <input type="checkbox" name="is_main" value="1" id="is_main" class="w-4 h-4 rounded text-blue-600">
                    <label for="is_main" class="text-sm font-medium">এটি প্রধান শাখা (Main Branch)</label>
                </div>
            </div>

            <div class="pt-4 border-t flex justify-end gap-3">
                <a href="{{ route('branches.index') }}" class="px-4 py-2 border rounded-lg text-sm">বাতিল</a>
                <button type="submit" class="px-5 py-2 bg-primary text-white rounded-lg text-sm font-semibold" style="background-color: var(--primary, #0ea5e9);">
                    শাখা সংরক্ষণ করুন
                </button>
            </div>
        </form>
    </div>
</div>
@endsection