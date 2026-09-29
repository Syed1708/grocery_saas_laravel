@extends('tyro-dashboard::layouts.app')

@section('title', 'শাখা ব্যবস্থাপনা (Branches)')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">শাখা তালিকা (Branch Outlets)</h1>
            <p class="text-sm text-slate-500">আপনার দোকানের সকল শাখা ও কাউন্টার এখান থেকে পরিচালনা করুন।</p>
        </div>
        <a href="{{ route('branches.create') }}" class="px-4 py-2 bg-primary text-white rounded-lg font-medium text-sm hover:opacity-90 transition flex items-center gap-2" style="background-color: var(--primary, #0ea5e9);">
            <span>+</span> নতুন শাখা যোগ করুন
        </a>
        
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="p-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-lg text-sm font-medium">
            {{ session('error') }}
        </div>
    @endif

    <!-- Table Card -->
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 text-slate-600 dark:text-slate-400 font-semibold">
                        <th class="p-4">শাখার নাম</th>
                        <th class="p-4">কোড</th>
                        <th class="p-4">মোবাইল</th>
                        <th class="p-4">ঠিকানা</th>
                        <th class="p-4 text-center">স্টাফ</th>
                        <th class="p-4 text-center">স্ট্যাটাস</th>
                        <th class="p-4 text-right">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($branches as $b)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/20 transition">
                            <td class="p-4 font-semibold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                <span>🏢</span>
                                <span>{{ $b->name }}</span>
                                @if($b->is_main)
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-blue-100 text-blue-700 uppercase">মেইন ব্রাঞ্চ</span>
                                @endif
                            </td>
                            <td class="p-4 font-mono text-xs">{{ $b->code }}</td>
                            <td class="p-4">{{ $b->phone ?? 'N/A' }}</td>
                            <td class="p-4 text-slate-500 max-w-xs truncate">{{ $b->address ?? 'N/A' }}</td>
                            <td class="p-4 text-center">
                                <span class="px-2 py-1 rounded-md bg-slate-100 dark:bg-slate-700 text-xs font-semibold">
                                    {{ $b->users_count }} জন
                                </span>
                            </td>
                            <td class="p-4 text-center">
                                @if($b->status === 'active')
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700">সক্রিয়</span>
                                @else
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-slate-100 text-slate-500">নিষ্ক্রিয়</span>
                                @endif
                            </td>
                            <td class="p-4 text-right space-x-2">
                                <a href="{{ route('branches.edit', ['branch' => $b->id]) }}" class="text-blue-600 hover:underline font-medium text-xs">এডিট</a>
                                @if(!$b->is_main)
                                    <form action="{{ route('branches.destroy', ['branch' => $b->id]) }}" method="POST" class="inline" onsubmit="return confirm('আপনি কি নিশ্চিত এই শাখাটি মুছে ফেলতে চান?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-600 hover:underline font-medium text-xs ml-2">ডিলিট</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-500">কোনো শাখা পাওয়া যায়নি।</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($branches->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-700">
                {{ $branches->links() }}
            </div>
        @endif
    </div>
</div>
@endsection