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

        <!-- 1. Staff Menu -->
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

        <!-- 2. Staff Grouped Modules -->
        @php
            $user = auth()->user();
            $userRoles = ($user && $user->roles) ? $user->roles->pluck('slug')->toArray() : [];
            $userPrivSlugs = [];
            if ($user && $user->roles) {
                $userPrivSlugs = $user->roles->flatMap(function ($role) {
                    return $role->privileges ? $role->privileges->pluck('slug') : collect();
                })->unique()->toArray();
            }

            $rawResources = $allResources ?? config('tyro-dashboard.resources', []);
            $groupedMenu = [];
            $ungroupedResources = [];

            foreach ($rawResources as $resKey => $item) {
                if (!is_array($item)) continue;

                // Strict Staff Privilege check
                $canAccess = true;
                if (!empty($item['roles'])) {
                    $canAccess = count(array_intersect($item['roles'], $userRoles)) > 0;
                }
                if ($canAccess && !empty($item['privilege'])) {
                    $canAccess = in_array($item['privilege'], $userPrivSlugs) || (method_exists($user, 'hasPrivilege') && $user->hasPrivilege($item['privilege']));
                }

                if (!$canAccess) continue;

                $url = '#';
                $isActive = false;

                if (isset($item['url'])) {
                    $url = url($item['url']);
                    $isActive = request()->is(ltrim($item['url'], '/') . '*');
                } elseif (isset($item['route']) && \Illuminate\Support\Facades\Route::has($item['route'])) {
                    $url = route($item['route']);
                    $isActive = request()->routeIs($item['route'] . '*');
                } elseif (isset($item['model']) || !is_numeric($resKey)) {
                    $url = route($dashboardRoute::name('resources.index'), ['resource' => (string)$resKey]);
                    $isActive = request()->is('*resources/' . $resKey . '*');
                }

                $item['resolved_url'] = $url;
                $item['is_active'] = $isActive;
                $item['res_key'] = $resKey;

                if (!empty($item['group'])) {
                    $grpName = $item['group'];
                    if (!isset($groupedMenu[$grpName])) {
                        $groupedMenu[$grpName] = [
                            'title' => $grpName,
                            'icon'  => $item['group_icon'] ?? null,
                            'items' => [],
                            'has_active' => false,
                        ];
                    }
                    if ($isActive) {
                        $groupedMenu[$grpName]['has_active'] = true;
                    }
                    $groupedMenu[$grpName]['items'][] = $item;
                } else {
                    $ungroupedResources[$resKey] = $item;
                }
            }
        @endphp

        <!-- Render Grouped Items -->
        @foreach($groupedMenu as $group)
            <div class="sidebar-section custom-accordion-group mb-1">
                <button type="button" 
                        onclick="toggleAccordion(this)" 
                        class="sidebar-link custom-group-btn {{ $group['has_active'] ? 'group-active' : '' }}"
                        aria-expanded="{{ $group['has_active'] ? 'true' : 'false' }}">
                    <div class="custom-group-title">
                        @if($group['icon'])
                            <span class="custom-parent-icon">{!! $group['icon'] !!}</span>
                        @else
                            <span class="custom-parent-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                </svg>
                            </span>
                        @endif
                        <span class="custom-group-name">{{ $group['title'] }}</span>
                    </div>

                    <svg class="custom-chevron {{ $group['has_active'] ? 'rotate-90' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                <div class="custom-subitems-wrapper" style="display: {{ $group['has_active'] ? 'flex' : 'none' }};">
                    @foreach($group['items'] as $subItem)
                        <a href="{{ $subItem['resolved_url'] }}" 
                           target="{{ $subItem['target'] ?? '_self' }}"
                           class="custom-subitem-link {{ $subItem['is_active'] ? 'active' : '' }}">
                            @if(isset($subItem['icon']))
                                <span class="custom-sub-icon">{!! $subItem['icon'] !!}</span>
                            @else
                                <span class="custom-sub-bullet"></span>
                            @endif
                            <span class="custom-sub-title">{{ $subItem['title'] }}</span>
                            @if($subItem['resolved_url'] === '#')
                                <span class="custom-badge-soon">soon</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach

        <!-- Render Ungrouped Resources for Staff -->
        @if(!empty($ungroupedResources))
        <div class="sidebar-section">
            <div class="sidebar-section-title">Resources</div>
            @foreach($ungroupedResources as $resKey => $resItem)
                <a href="{{ $resItem['resolved_url'] }}" 
                   target="{{ $resItem['target'] ?? '_self' }}"
                   class="sidebar-link {{ $resItem['is_active'] ? 'active' : '' }}">
                    @if(isset($resItem['icon']))
                        <span class="custom-parent-icon mr-2">{!! $resItem['icon'] !!}</span>
                    @else
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    @endif
                    <span>{{ $resItem['title'] ?? ucfirst($resKey) }}</span>
                </a>
            @endforeach
        </div>
        @endif
    </nav>
</aside>

<style>
    .custom-group-btn { width: 100% !important; cursor: pointer !important; user-select: none; display: flex !important; align-items: center !important; justify-content: space-between !important; background: transparent; border: none; padding: 0.5rem 0.75rem; border-radius: 0.375rem; transition: background-color 0.15s ease, color 0.15s ease; }
    .custom-group-btn:hover { background-color: rgba(148, 163, 184, 0.08); }
    .custom-group-btn.group-active { font-weight: 600; color: var(--primary, #0ea5e9); }
    .custom-group-title { display: flex; align-items: center; gap: 0.625rem; font-size: 0.875rem; font-weight: 500; }
    .custom-parent-icon { width: 18px; height: 18px; min-width: 18px; display: inline-flex; align-items: center; justify-content: center; }
    .custom-parent-icon svg { width: 18px !important; height: 18px !important; max-width: 18px !important; max-height: 18px !important; }
    .custom-chevron { width: 14px; height: 14px; min-width: 14px; transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1); opacity: 0.6; }
    .custom-chevron.rotate-90 { transform: rotate(90deg); opacity: 0.9; }
    .custom-subitems-wrapper { flex-direction: column; gap: 2px; margin-left: 1.35rem; padding-left: 0.75rem; border-left: 1.5px solid rgba(148, 163, 184, 0.18); margin-top: 3px; margin-bottom: 6px; }
    .custom-subitem-link { display: flex; align-items: center; gap: 0.5rem; padding: 0.375rem 0.625rem; font-size: 0.8125rem; color: inherit; opacity: 0.8; border-radius: 0.375rem; text-decoration: none; transition: all 0.15s ease; }
    .custom-subitem-link:hover { opacity: 1; background-color: rgba(148, 163, 184, 0.12); transform: translateX(2px); }
    .custom-subitem-link.active { opacity: 1; font-weight: 600; background-color: rgba(14, 165, 233, 0.1); color: var(--primary, #0ea5e9); }
    .custom-sub-icon { width: 15px; height: 15px; min-width: 15px; display: inline-flex; align-items: center; justify-content: center; opacity: 0.75; }
    .custom-sub-icon svg { width: 15px !important; height: 15px !important; max-width: 15px !important; max-height: 15px !important; }
    .custom-sub-bullet { width: 5px; height: 5px; min-width: 5px; border-radius: 50%; background-color: currentColor; opacity: 0.45; margin-left: 2px; margin-right: 4px; }
    .custom-subitem-link:hover .custom-sub-bullet, .custom-subitem-link.active .custom-sub-bullet { opacity: 1; background-color: var(--primary, #0ea5e9); }
    .custom-badge-soon { margin-left: auto; font-size: 9px; font-weight: 600; text-transform: uppercase; padding: 1px 5px; border-radius: 4px; background-color: rgba(148, 163, 184, 0.15); opacity: 0.6; }
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