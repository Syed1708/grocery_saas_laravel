@extends('tyro-dashboard::layouts.admin')

@section('title', 'Shop Settings')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Settings</span>
<span class="breadcrumb-separator">/</span>
<span>Shop Subscription</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">SaaS Software License & Subscription (সফটওয়্যার লাইসেন্স)</h1>
        </div>
    </div>
</div>

<!-- License & Subscription Management Card -->
<div class="card" style="margin-top: 1.5rem; border: 1px solid var(--border);">
    <div class="card-header">
        <h3 class="card-title">🔑 SaaS Software License & Subscription (সফটওয়্যার লাইসেন্স)</h3>
    </div>
    <div class="card-body" style="display: flex; flex-direction: column; gap: 1rem;">
        

        @if($license)
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; background: var(--muted, rgba(148,163,184,0.05)); padding: 1rem; border-radius: 8px;">
                <div>
                    <span style="font-size: 0.75rem; color: var(--muted-foreground); text-transform: uppercase;">Client / Shop Name</span>
                    <strong style="display: block; font-size: 0.95rem;">{{ $license->client_name }}</strong>
                </div>
                <div>
                    <span style="font-size: 0.75rem; color: var(--muted-foreground); text-transform: uppercase;">Expiry Date (মেয়াদের শেষ তারিখ)</span>
                    <strong style="display: block; font-size: 0.95rem; font-family: monospace; color: {{ $license->isExpired() ? 'var(--danger, #ef4444)' : 'var(--success, #10b981)' }};">
                        {{ $license->expires_at->format('d M, Y') }} ({{ $license->daysRemaining() }} days left)
                    </strong>
                </div>
                <div>
                    <span style="font-size: 0.75rem; color: var(--muted-foreground); text-transform: uppercase;">Submitted TrxID (কাস্টমার প্রদত্ত ট্রানজেকশন আইডি)</span>
                    <strong style="display: block; font-size: 1rem; font-family: monospace; color: #f59e0b;">
                        {{ $license->last_trx_id ?? 'None submitted' }}
                    </strong>
                </div>
            </div>

            @php
                $user = auth()->user();
            @endphp
            @if($user && ($user->isVendor()))
            
           <!-- Manual Renewal Form -->
            <form action="{{ route('subscription.manual-renew') }}" method="POST" style="display: flex; gap: 1rem; align-items: flex-end; margin-top: 0.5rem;">
                @csrf
                <div class="form-group" style="margin: 0; min-width: 200px;">
                    <label class="form-label" style="font-size: 0.75rem;">Extend Duration (মেদ বৃদ্ধি করুন)</label>
                    <select name="months" class="form-select" required>
                        <option value="1">1 Month (+30 Days)</option>
                        <option value="3">3 Months (+90 Days)</option>
                        <option value="6">6 Months (+180 Days)</option>
                        <option value="12">1 Year (+365 Days)</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="font-weight: 700;">
                    ✓ Verify TrxID & Renew License
                </button>
            </form>
                
            @endif
        @else
            <p style="color: var(--muted-foreground); font-size: 0.875rem;">No license record found in database.</p>
        @endif

    </div>
</div>
@endsection