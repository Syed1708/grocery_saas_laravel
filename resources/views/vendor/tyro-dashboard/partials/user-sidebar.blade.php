<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="{{ route($dashboardRoute::name('index')) }}" class="sidebar-logo">
            <div class="sidebar-logo-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
            <span class="sidebar-logo-text">{{ $branding['app_name'] ?? config('app.name', 'Laravel') }}</span>
        </a>
        @if(config('tyro-dashboard.collapsible_sidebar', false))
        <button class="sidebar-collapse-btn" onclick="toggleSidebarCollapse()" aria-label="Collapse sidebar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
        @endif
    </div>
    @if(config('tyro-dashboard.collapsible_sidebar', false))
    <button class="sidebar-expand-btn" onclick="toggleSidebarCollapse()" aria-label="Expand sidebar">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
        </svg>
    </button>
    @endif

    <nav class="sidebar-nav sidebar-accordion"
        data-sidebar-accordion
        data-sidebar-accordion-compact="{{ config('tyro-dashboard.branding.sidebar_accordion_compact', false) ? 'true' : 'false' }}">

        <!-- 1. Staff Main Menu -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">Menu</div>
            <a href="{{ route($dashboardRoute::name('index')) }}" class="sidebar-link {{ request()->routeIs($dashboardRoute::pattern('index')) ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Dashboard
            </a>
            <a href="{{ route($dashboardRoute::name('profile')) }}" class="sidebar-link {{ request()->routeIs($dashboardRoute::pattern('profile*')) ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                My Profile
            </a>

            @if(config('tyro-dashboard.features.invitation_system', true) && \Illuminate\Support\Facades\Route::has($dashboardRoute::name('invitations.index')))
            <a href="{{ route($dashboardRoute::name('invitations.index')) }}" class="sidebar-link {{ request()->routeIs($dashboardRoute::pattern('invitations.index')) ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                </svg>
                My Invitation Link
            </a>
            @endif

            @if(!empty($commonMenuItems))
                @foreach($commonMenuItems as $item)
                    <a href="{{ route($item['route'] ?? '#') }}" class="sidebar-link {{ request()->routeIs($item['route'] ?? '') ? 'active' : '' }}">
                        @if(isset($item['icon'])) {!! $item['icon'] !!} @else
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                        @endif
                        {{ $item['title'] ?? 'Menu Item' }}
                    </a>
                @endforeach
            @endif

            @if(!empty($userMenuItems))
                @foreach($userMenuItems as $item)
                    <a href="{{ route($item['route'] ?? '#') }}" class="sidebar-link {{ request()->routeIs($item['route'] ?? '') ? 'active' : '' }}">
                        @if(isset($item['icon'])) {!! $item['icon'] !!} @else
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                        @endif
                        {{ $item['title'] ?? 'Menu Item' }}
                    </a>
                @endforeach
            @endif
        </div>

        <!-- 2. Staff Allowed Groups -->
        @php
            $user = auth()->user();
            
            // 1. Roles array from relation
            $userRoles = ($user && $user->roles) ? $user->roles->pluck('slug')->toArray() : [];

            // 2. Safe Privilege collection through roles (Never calls $user->privileges directly)
            $userPrivSlugs = [];
            if ($user && $user->roles) {
                $userPrivSlugs = $user->roles->flatMap(function ($role) {
                    return $role->privileges ? $role->privileges->pluck('slug') : collect();
                })->unique()->toArray();
            }

            // 3. Read directly from config
            $allConfigResources = config('tyro-dashboard.resources', []);
        @endphp

        @foreach($allConfigResources as $key => $resource)
            {{-- Grouped Modules --}}
            @if(isset($resource['group']) && isset($resource['items']))
                @php
                    $canSeeGroup = true;

                    // A: Role check (if specified on group)
                    if (isset($resource['roles']) && !empty($resource['roles'])) {
                        $hasRole = false;
                        foreach ($resource['roles'] as $role) {
                            if (in_array($role, $userRoles)) { 
                                $hasRole = true; 
                                break; 
                            }
                        }
                        if (!$hasRole) { 
                            $canSeeGroup = false; 
                        }
                    }

                    // B: Privilege check (checks array, hasPrivilege method, or Laravel Gate)
                    if ($canSeeGroup && isset($resource['privilege']) && !empty($resource['privilege'])) {
                        $priv = $resource['privilege'];
                        $hasPriv = in_array($priv, $userPrivSlugs) || (method_exists($user, 'hasPrivilege') && $user->hasPrivilege($priv));
                        if (!$hasPriv) {
                            $canSeeGroup = false;
                        }
                    }

                    // Check if any child item is active (Auto-expand accordion)
                    $isAnyChildActive = false;
                    foreach($resource['items'] as $chk) {
                        if (isset($chk['url']) && request()->is(ltrim($chk['url'], '/') . '*')) {
                            $isAnyChildActive = true;
                        } elseif (isset($chk['route']) && \Illuminate\Support\Facades\Route::has($chk['route']) && request()->routeIs($chk['route'] . '*')) {
                            $isAnyChildActive = true;
                        } elseif (isset($chk['resource']) && request()->is('*resources/' . $chk['resource'] . '*')) {
                            $isAnyChildActive = true;
                        }
                    }
                @endphp

                @if($canSeeGroup)
                <div class="sidebar-section custom-accordion-group mb-1">
                    <!-- Group Header Button -->
                    <button type="button" 
                            onclick="toggleAccordion(this)" 
                            class="sidebar-link custom-group-btn {{ $isAnyChildActive ? 'group-active' : '' }}"
                            aria-expanded="{{ $isAnyChildActive ? 'true' : 'false' }}">
                        <div class="custom-group-title">
                            @if(isset($resource['icon']))
                                <span class="custom-parent-icon">{!! $resource['icon'] !!}</span>
                            @else
                                <span class="custom-parent-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                    </svg>
                                </span>
                            @endif
                            <span class="custom-group-name">{{ $resource['group'] }}</span>
                        </div>

                        <!-- Animated Chevron Arrow -->
                        <svg class="custom-chevron {{ $isAnyChildActive ? 'rotate-90' : '' }}" 
                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>

                    <!-- Sub-items List with Indented Tree Line -->
                    <div class="custom-subitems-wrapper" style="display: {{ $isAnyChildActive ? 'flex' : 'none' }};">
                        @foreach($resource['items'] as $subItem)
                            @php
                                $canSeeItem = true;

                                // Subitem role check
                                if (isset($subItem['roles']) && !empty($subItem['roles'])) {
                                    $hasSubRole = false;
                                    foreach ($subItem['roles'] as $sRole) {
                                        if (in_array($sRole, $userRoles)) { 
                                            $hasSubRole = true; 
                                            break; 
                                        }
                                    }
                                    if (!$hasSubRole) { 
                                        $canSeeItem = false; 
                                    }
                                }

                                // Subitem privilege check
                                if ($canSeeItem && isset($subItem['privilege']) && !empty($subItem['privilege'])) {
                                    $subPriv = $subItem['privilege'];
                                    $hasSubPriv = in_array($subPriv, $userPrivSlugs) || (method_exists($user, 'hasPrivilege') && $user->hasPrivilege($subPriv));
                                    if (!$hasSubPriv) {
                                        $canSeeItem = false;
                                    }
                                }

                                $linkHref = '#';
                                $isActive = false;

                                if (isset($subItem['url'])) {
                                    $linkHref = url($subItem['url']);
                                    $isActive = request()->is(ltrim($subItem['url'], '/') . '*');
                                } elseif (isset($subItem['route']) && \Illuminate\Support\Facades\Route::has($subItem['route'])) {
                                    $linkHref = route($subItem['route']);
                                    $isActive = request()->routeIs($subItem['route'] . '*');
                                } elseif (isset($subItem['resource'])) {
                                    $linkHref = route($dashboardRoute::name('resources.index'), $subItem['resource']);
                                    $isActive = request()->is('*resources/' . $subItem['resource'] . '*');
                                }
                            @endphp

                            @if($canSeeItem)
                            <a href="{{ $linkHref }}" 
                               target="{{ $subItem['target'] ?? '_self' }}"
                               class="custom-subitem-link {{ $isActive ? 'active' : '' }}">
                                @if(isset($subItem['icon']))
                                    <span class="custom-sub-icon">{!! $subItem['icon'] !!}</span>
                                @else
                                    <span class="custom-sub-bullet"></span>
                                @endif

                                <span class="custom-sub-title">{{ $subItem['title'] }}</span>

                                @if($linkHref === '#')
                                    <span class="custom-badge-soon">soon</span>
                                @endif
                            </a>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endif

            
            {{-- Fallback: Traditional Resource for regular users --}}
            @elseif(isset($resource['title']))
                @php
                    $canAccessResource = true;
                    if (isset($resource['roles']) && !empty($resource['roles'])) {
                        $hasResRole = false;
                        foreach ($resource['roles'] as $role) {
                            if (in_array($role, $userRoles)) { $hasResRole = true; break; }
                        }
                        if (!$hasResRole) { $canAccessResource = false; }
                    }
                @endphp

                @if($canAccessResource)
                <div class="sidebar-section">
                    <div class="sidebar-section-title">Resources</div>
                    <a href="{{ route($dashboardRoute::name('resources.index'), $key) }}" class="sidebar-link {{ request()->is('*resources/'.$key.'*') ? 'active' : '' }}">
                        {{ $resource['title'] }}
                    </a>
                </div>
                @endif
            @endif
        @endforeach

        
    </nav>
</aside>

<!-- Scoped Styling matching Tyro Dashboard shadcn Theme -->
<style>
    .custom-group-btn {
        width: 100% !important;
        cursor: pointer !important;
        user-select: none;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        background: transparent;
        border: none;
        padding: 0.5rem 0.75rem;
        border-radius: 0.375rem;
        transition: background-color 0.15s ease, color 0.15s ease;
    }
    .custom-group-btn:hover {
        background-color: rgba(148, 163, 184, 0.08);
    }
    .custom-group-btn.group-active {
        font-weight: 600;
        color: var(--primary, #0ea5e9);
    }
    .custom-group-title {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        font-size: 0.875rem;
        font-weight: 500;
    }
    .custom-parent-icon {
        width: 18px;
        height: 18px;
        min-width: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .custom-parent-icon svg {
        width: 18px !important;
        height: 18px !important;
        max-width: 18px !important;
        max-height: 18px !important;
    }
    .custom-chevron {
        width: 14px;
        height: 14px;
        min-width: 14px;
        transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        opacity: 0.6;
    }
    .custom-chevron.rotate-90 {
        transform: rotate(90deg);
        opacity: 0.9;
    }
    .custom-subitems-wrapper {
        flex-direction: column;
        gap: 2px;
        margin-left: 1.35rem;
        padding-left: 0.75rem;
        border-left: 1.5px solid rgba(148, 163, 184, 0.18);
        margin-top: 3px;
        margin-bottom: 6px;
    }
    .custom-subitem-link {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.375rem 0.625rem;
        font-size: 0.8125rem;
        color: inherit;
        opacity: 0.8;
        border-radius: 0.375rem;
        text-decoration: none;
        transition: all 0.15s ease;
    }
    .custom-subitem-link:hover {
        opacity: 1;
        background-color: rgba(148, 163, 184, 0.12);
        transform: translateX(2px);
    }
    .custom-subitem-link.active {
        opacity: 1;
        font-weight: 600;
        background-color: rgba(14, 165, 233, 0.1);
        color: var(--primary, #0ea5e9);
    }
    .custom-sub-icon {
        width: 15px;
        height: 15px;
        min-width: 15px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        opacity: 0.75;
    }
    .custom-sub-icon svg {
        width: 15px !important;
        height: 15px !important;
        max-width: 15px !important;
        max-height: 15px !important;
    }
    .custom-sub-bullet {
        width: 5px;
        height: 5px;
        min-width: 5px;
        border-radius: 50%;
        background-color: currentColor;
        opacity: 0.45;
        margin-left: 2px;
        margin-right: 4px;
    }
    .custom-subitem-link:hover .custom-sub-bullet,
    .custom-subitem-link.active .custom-sub-bullet {
        opacity: 1;
        background-color: var(--primary, #0ea5e9);
    }
    .custom-badge-soon {
        margin-left: auto;
        font-size: 9px;
        font-weight: 600;
        text-transform: uppercase;
        padding: 1px 5px;
        border-radius: 4px;
        background-color: rgba(148, 163, 184, 0.15);
        opacity: 0.6;
    }
</style>

<script>
    function toggleAccordion(btn) {
        const group = btn.closest('.custom-accordion-group');
        const content = group.querySelector('.custom-subitems-wrapper');
        const chevron = group.querySelector('.custom-chevron');

        if (content.style.display === 'none' || content.style.display === '') {
            content.style.display = 'flex';
            chevron.classList.add('rotate-90');
            btn.setAttribute('aria-expanded', 'true');
        } else {
            content.style.display = 'none';
            chevron.classList.remove('rotate-90');
            btn.setAttribute('aria-expanded', 'false');
        }
    }
</script>