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

        <!-- 1. Main Menu -->
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

        <!-- 2. 🚀 Grouped Modules with Subitem Icons & Modern UI -->
        @php
            $user = auth()->user();
            $isSuperAdmin = $user && (
                (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) ||
                (method_exists($user, 'hasRole') && $user->hasRole('super-admin')) ||
                is_null($user->tenant_id)
            );
            $resourcesConfig = $allResources ?? config('tyro-dashboard.resources', []);
        @endphp

        @if(!empty($resourcesConfig))
            @foreach($resourcesConfig as $groupIndex => $resource)
                @if(isset($resource['group']) && isset($resource['items']))
                    @php
                        // Check Group-level Permission
                        $canSeeGroup = $isSuperAdmin;
                        if (!$canSeeGroup) {
                            $canSeeGroup = true;
                            if (!empty($resource['roles']) && method_exists($user, 'hasAnyRole') && !$user->hasAnyRole($resource['roles'])) {
                                $canSeeGroup = false;
                            }
                            if (!empty($resource['privilege']) && method_exists($user, 'hasPrivilege') && !$user->hasPrivilege($resource['privilege'])) {
                                $canSeeGroup = false;
                            }
                        }

                        // Determine if ANY child item is active (Auto-expand)
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
                        <!-- Group Header (Cursor pointer + Hover styles) -->
                        <button type="button" 
                                onclick="toggleAccordion(this)" 
                                class="sidebar-link custom-group-btn {{ $isAnyChildActive ? 'group-active' : '' }}"
                                aria-expanded="{{ $isAnyChildActive ? 'true' : 'false' }}">
                            <div class="custom-group-title">
                                @if(isset($resource['icon']))
                                    <span class="custom-parent-icon">
                                        {!! $resource['icon'] !!}
                                    </span>
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

                        <!-- Sub-items List with Sleek Guide Line -->
                        <div class="custom-subitems-wrapper" 
                             style="display: {{ $isAnyChildActive ? 'flex' : 'none' }};">
                            @foreach($resource['items'] as $subItem)
                                @php
                                    $canSeeItem = $isSuperAdmin;
                                    if (!$canSeeItem) {
                                        $canSeeItem = true;
                                        if (!empty($subItem['roles']) && method_exists($user, 'hasAnyRole') && !$user->hasAnyRole($subItem['roles'])) {
                                            $canSeeItem = false;
                                        }
                                        if (!empty($subItem['privilege']) && method_exists($user, 'hasPrivilege') && !$user->hasPrivilege($subItem['privilege'])) {
                                            $canSeeItem = false;
                                        }
                                    }

                                    // URL Resolution
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
                                    <!-- Subitem Icon (or minimal bullet dot) -->
                                    @if(isset($subItem['icon']))
                                        <span class="custom-sub-icon">
                                            {!! $subItem['icon'] !!}
                                        </span>
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

                {{-- B: Fallback for standard Tyro Resources --}}
                @elseif(isset($resource['title']))
                    <div class="sidebar-section">
                        <div class="sidebar-section-title">Resources</div>
                        <a href="{{ route($dashboardRoute::name('resources.index'), $groupIndex) }}" class="sidebar-link {{ request()->is('*resources/'.$groupIndex.'*') ? 'active' : '' }}">
                            {{ $resource['title'] }}
                        </a>
                    </div>
                @endif
            @endforeach
        @endif

        <!-- 3. Administration Menu -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">Administration</div>
            <a href="{{ route($dashboardRoute::name('users.index')) }}" class="sidebar-link {{ request()->routeIs($dashboardRoute::pattern('users.*')) ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                Users
            </a>
            <a href="{{ route($dashboardRoute::name('roles.index')) }}" class="sidebar-link {{ request()->routeIs($dashboardRoute::pattern('roles.*')) ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                Roles
            </a>
            <a href="{{ route($dashboardRoute::name('privileges.index')) }}" class="sidebar-link {{ request()->routeIs($dashboardRoute::pattern('privileges.*')) ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
                Privileges
            </a>
            @if(config('tyro-dashboard.features.invitation_system', true))
            <a href="{{ route($dashboardRoute::name('invitations.admin.index')) }}" class="sidebar-link {{ request()->routeIs($dashboardRoute::pattern('invitations.admin.*')) ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                </svg>
                Invitation Links
            </a>
            @endif

            @php
                $showAuditLogsMenu = false;
                if (config('tyro-dashboard.features.audit_logs', true) && config('tyro.audit.enabled', true) && class_exists('\HasinHayder\Tyro\Models\AuditLog')) {
                    try {
                        $showAuditLogsMenu = \Illuminate\Support\Facades\Schema::hasTable(config('tyro.tables.audit_logs', 'tyro_audit_logs'));
                    } catch (\Throwable $e) {
                        $showAuditLogsMenu = false;
                    }
                }
            @endphp

            @if($showAuditLogsMenu)
            <a href="{{ route($dashboardRoute::name('audits.index')) }}" class="sidebar-link {{ request()->routeIs($dashboardRoute::pattern('audits.*')) ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Audit Logs
            </a>
            @endif

            @if(!empty($adminMenuItems))
                @foreach($adminMenuItems as $item)
                    <a href="{{ route($item['route'] ?? '#') }}" class="sidebar-link {{ request()->routeIs($item['route'] ?? '') ? 'active' : '' }}">
                        @if(isset($item['icon'])) {!! $item['icon'] !!} @else
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                        @endif
                        {{ $item['title'] ?? 'Menu Item' }}
                    </a>
                @endforeach
            @endif
        </div>

        <!-- 4. Examples -->
        @if(!config('tyro-dashboard.disable_examples', false) && !app()->environment('production'))
        <div class="sidebar-section">
            <div class="sidebar-section-title">Examples</div>
            <a href="{{ route($dashboardRoute::name('components')) }}" class="sidebar-link {{ (request()->routeIs($dashboardRoute::pattern('components')) || request()->routeIs($dashboardRoute::pattern('examples.components'))) ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h3a2 2 0 012 2v3a2 2 0 01-2 2H6a2 2 0 01-2-2V6z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 6a2 2 0 012-2h3a2 2 0 012 2v3a2 2 0 01-2 2h-3a2 2 0 01-2-2V6z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 15a2 2 0 012-2h3a2 2 0 012 2v3a2 2 0 01-2 2H6a2 2 0 01-2-2v-3z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 15a2 2 0 012-2h3a2 2 0 012 2v3a2 2 0 01-2 2h-3a2 2 0 01-2-2v-3z" />
                </svg>
                Dashboard Components
            </a>

            <a href="{{ route($dashboardRoute::name('widgets')) }}" class="sidebar-link {{ (request()->routeIs($dashboardRoute::pattern('widgets')) || request()->routeIs($dashboardRoute::pattern('examples.widgets'))) ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18" /><path stroke-linecap="round" stroke-linejoin="round" d="M3 12h18" /><path stroke-linecap="round" stroke-linejoin="round" d="M5 5h6v6H5z" /><path stroke-linecap="round" stroke-linejoin="round" d="M13 13h6v6h-6z" />
                </svg>
                Widgets
            </a>

            @if(class_exists('HasinHayder\\TyroDashboardComponents\\TyroDashboardComponentsServiceProvider'))
            <a href="{{ route($dashboardRoute::name('x-components')) }}" class="sidebar-link {{ request()->routeIs($dashboardRoute::pattern('x-components')) ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Form Components
            </a>
            @endif
        </div>
        @endif
    </nav>
</aside>

<!-- Scoped Styling matching Tyro Dashboard shadcn Theme -->
<style>
    /* Group Header Button */
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

    /* Subitems Tree Line Hierarchy */
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

    /* Subitem Icon or Bullet */
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