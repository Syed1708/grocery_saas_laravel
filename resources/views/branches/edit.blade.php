@extends('tyro-dashboard::layouts.app')

@section('title', 'শাখা এডিট করুন')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold">শাখা তথ্য পরিবর্তন</h1>
        <a href="{{ route('branches.index') }}" class="text-sm text-slate-500 hover:underline">← ফিরে যান</a>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
        <form action="{{ route('branches.update', ['branch' => $branch->id]) }}" method="POST" class="space-y-4">
            
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-semibold mb-1">শাখার নাম <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $branch->name) }}" required class="w-full px-3 py-2 border rounded-lg text-sm">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">শাখা কোড</label>
                    <input type="text" name="code" value="{{ old('code', $branch->code) }}" required class="w-full px-3 py-2 border rounded-lg text-sm font-mono">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">ফোন নম্বর</label>
                    <input type="text" name="phone" value="{{ old('phone', $branch->phone) }}" class="w-full px-3 py-2 border rounded-lg text-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">ঠিকানা</label>
                <textarea name="address" rows="2" class="w-full px-3 py-2 border rounded-lg text-sm">{{ old('address', $branch->address) }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">স্ট্যাটাস</label>
                    <select name="status" class="w-full px-3 py-2 border rounded-lg text-sm">
                        <option value="active" {{ $branch->status === 'active' ? 'selected' : '' }}>সক্রিয়</option>
                        <option value="inactive" {{ $branch->status === 'inactive' ? 'selected' : '' }}>নিষ্ক্রিয়</option>
                    </select>
                </div>
                <div class="flex items-center gap-2 pt-6">
                    <input type="checkbox" name="is_main" value="1" id="is_main" {{ $branch->is_main ? 'checked' : '' }} class="w-4 h-4 rounded text-blue-600">
                    <label for="is_main" class="text-sm font-medium">প্রধান শাখা (Main Branch)</label>
                </div>
            </div>

            <div class="pt-4 border-t flex justify-end gap-3">
                <a href="{{ route('branches.index') }}" class="px-4 py-2 border rounded-lg text-sm">বাতিল</a>
                <button type="submit" class="px-5 py-2 bg-primary text-white rounded-lg text-sm font-semibold" style="background-color: var(--primary, #0ea5e9);">
                    আপডেট করুন
                </button>
            </div>
        </form>
    </div>
</div>
@endsection