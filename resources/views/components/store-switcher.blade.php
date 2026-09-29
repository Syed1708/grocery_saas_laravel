@php
    $user = auth()->user();
    $activeBranchId = session('active_branch_id') ?? ($user->branch_id ?? \App\Models\Branch::where('is_main', true)->value('id') ?? \App\Models\Branch::first()?->id);
    $activeBranch = \App\Models\Branch::find($activeBranchId);
    $allBranches = \App\Models\Branch::where('status', 'active')->get();
    
    // Only Admin / Owner can switch branches! Cashier is LOCKED.
    $canSwitchBranch = $user && ($user->isSuperAdmin() || $user->hasRole('owner'));
@endphp

<div class="branch-switcher-container" id="branchSwitcherContainer" style="position: relative; display: inline-flex; align-items: center;">

    @if($canSwitchBranch)
        <!-- 🟢 1. ADMIN / OWNER: CLICKABLE DROPDOWN -->
        <button type="button" 
                onclick="toggleBranchDropdown(event)"
                class="store-switcher-btn"
                style="display: flex; align-items: center; gap: 6px; height: 36px; padding: 4px 8px; border-radius: 6px; border: 1px solid var(--border, #e2e8f0); background: var(--background, #f8fafc); color: var(--foreground, #0f172a); cursor: pointer; font-size: 13px;">
            
            <span style="font-size: 14px; display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 4px; background: rgba(14, 165, 233, 0.1);">
                🏢
            </span>

            <div style="display: flex; flex-direction: column; text-align: left; max-width: 140px;">
                <span style="font-weight: 600; line-height: 1.1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 12px;">
                    {{ $activeBranch ? $activeBranch->name : 'শাখা সিলেক্ট করুন' }}
                </span>
                <span style="font-size: 10px; color: var(--muted-foreground, #64748b); line-height: 1;">
                    {{ $activeBranch?->is_main ? 'মেইন শাখা' : 'আউটলেট' }}
                </span>
            </div>

            <svg class="branch-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 12px; height: 12px; opacity: 0.6; transition: transform 0.2s;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <!-- Dropdown Menu -->
        <div class="branch-dropdown-menu" id="branchDropdownMenu" style="display: none; position: absolute; top: calc(100% + 6px); right: 0; width: 260px; background: var(--card, #ffffff); border: 1px solid var(--border, #e2e8f0); border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); z-index: 100; padding: 6px;">
            <div style="padding: 4px 8px 6px; font-size: 10px; font-weight: 700; text-transform: uppercase; color: var(--muted-foreground, #64748b);">
                শাখা পরিবর্তন (Switch Branch)
            </div>

            <div style="max-height: 200px; overflow-y: auto;">
                @foreach($allBranches as $b)
                    <form action="{{ route('branches.switch', ['branch' => $b->id]) }}" method="POST" style="margin: 0;">
                        @csrf
                        <button type="submit" style="width: 100%; display: flex; align-items: center; justify-content: space-between; padding: 6px 8px; border-radius: 6px; border: none; background: {{ ($activeBranch && $activeBranch->id === $b->id) ? 'rgba(14, 165, 233, 0.12)' : 'transparent' }}; color: inherit; cursor: pointer; font-size: 12px; text-align: left;">
                            <div style="display: flex; align-items: center; gap: 8px; overflow: hidden;">
                                <span>🏢</span>
                                <div style="overflow: hidden;">
                                    <div style="font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $b->name }}</div>
                                    <div style="font-size: 10px; opacity: 0.6; font-mono: monospace;">{{ $b->code }} • {{ $b->is_main ? 'Main' : 'Outlet' }}</div>
                                </div>
                            </div>
                            @if($activeBranch && $activeBranch->id === $b->id) 
                                <span style="color: #0ea5e9; font-weight: bold;">✓</span> 
                            @endif
                        </button>
                    </form>
                @endforeach
            </div>
        </div>

    @else
        <!-- 🔒 2. CASHIER / STAFF: STATIC LOCKED BADGE (NO DROPDOWN!) -->
        <div style="display: flex; align-items: center; gap: 6px; height: 36px; padding: 4px 10px; border-radius: 6px; border: 1px solid var(--border, #e2e8f0); background: var(--background, #f8fafc); color: var(--foreground, #0f172a);">
            <span style="font-size: 14px; display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 4px; background: rgba(16, 185, 129, 0.12); color: #059669;">
                🏢
            </span>
            <div style="display: flex; flex-direction: column; text-align: left; max-width: 140px;">
                <span style="font-weight: 600; line-height: 1.1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 12px;">
                    {{ $activeBranch ? $activeBranch->name : 'আপনার শাখা' }}
                </span>
                <span style="font-size: 10px; color: #059669; font-weight: 600; line-height: 1;">
                    🔒 নির্ধারিত শাখা (Locked)
                </span>
            </div>
        </div>
    @endif

</div>

<script>
    function toggleBranchDropdown(e) {
        e.stopPropagation();
        const menu = document.getElementById('branchDropdownMenu');
        const arrow = document.querySelector('.branch-chevron');
        if (menu) {
            const isHidden = menu.style.display === 'none' || menu.style.display === '';
            menu.style.display = isHidden ? 'block' : 'none';
            if (arrow) arrow.style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';
        }
    }

    document.addEventListener('click', function(e) {
        const container = document.getElementById('branchSwitcherContainer');
        const menu = document.getElementById('branchDropdownMenu');
        const arrow = document.querySelector('.branch-chevron');
        if (container && !container.contains(e.target) && menu) {
            menu.style.display = 'none';
            if (arrow) arrow.style.transform = 'rotate(0deg)';
        }
    });
</script>