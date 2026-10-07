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

@php
    $rawResources = config('tyro-dashboard.resources', []);
    $user = auth()->user();

    $userRoles = $user && method_exists($user, 'tyroRoleSlugs') 
        ? $user->tyroRoleSlugs() 
        : ($user && $user->roles ? $user->roles->pluck('slug')->toArray() : []);

    $adminRoles = config('tyro-dashboard.admin_roles', ['admin', 'super-admin', 'owner']);
    $isAdmin = $user && (
        (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) ||
        count(array_intersect($userRoles, $adminRoles)) > 0 ||
        is_null($user->branch_id)
    );

    $userPrivileges = [];
    if ($user && !$isAdmin) {
        $userPrivileges = $user->roles()
            ->with('privileges')
            ->get()
            ->flatMap(function ($role) {
                return $role->privileges ? $role->privileges->pluck('slug') : [];
            })
            ->unique()
            ->toArray();
    }

    // 🔍 1. LOG USER & PRIVILEGES TO storage/logs/laravel.log
    \Illuminate\Support\Facades\Log::info('--- SIDEBAR DEBUG ---', [
        'user_email' => $user?->email,
        'user_roles' => $userRoles,
        'isAdmin'    => $isAdmin,
        'privileges' => $userPrivileges,
    ]);

    $resourcesByGroup = [];

    foreach ($rawResources as $key => $resource) {
        if (!is_array($resource)) {
            continue;
        }

        $canAccess = true;
        $hasRoles = isset($resource['roles']) && !empty($resource['roles']);
        $hasPrivilege = isset($resource['privilege']) && !empty($resource['privilege']);

        if ($isAdmin) {
            $canAccess = true;
        } elseif ($hasRoles) {
            $canAccess = count(array_intersect($resource['roles'], $userRoles)) > 0;
        } elseif ($hasPrivilege) {
            $canAccess = in_array($resource['privilege'], $userPrivileges);
        } else {
            $canAccess = true;
        }

        // 🔍 2. LOG EACH RESOURCE EVALUATION
        \Illuminate\Support\Facades\Log::info("Resource Check [{$key}]", [
            'title'              => $resource['title'] ?? $key,
            'required_privilege' => $resource['privilege'] ?? 'none',
            'canAccess'          => $canAccess,
        ]);

        if ($canAccess) {
            $groupName = $resource['group'] ?? 'Resources';
            $resourcesByGroup[$groupName][$key] = $resource;
        }
    }
@endphp

<!-- 🚀 BROWSER CONSOLE DEBUGGER -->
<script>
    console.group("🏪 POS SaaS Sidebar Debugger");
    console.log("Current Route Path:", "{{ request()->path() }}");
    console.log("Logged-in User:", "{{ $user?->email ?? 'Guest' }}");
    console.log("Is Admin:", @json($isAdmin));
    console.log("User Roles:", @json($userRoles));
    console.log("User Privileges:", @json($userPrivileges));
    console.log("Generated resourcesByGroup:", @json($resourcesByGroup));
    console.groupEnd();
</script>

        {{-- Render ONLY groups that actually have visible items for this user --}}
        @if (!empty($resourcesByGroup))
            @foreach ($resourcesByGroup as $groupName => $groupResources)
                <div class="sidebar-section">
                    <div class="sidebar-section-title">{{ $groupName }}</div>
                    @foreach ($groupResources as $key => $resource)
                        @php
                            if (isset($resource['url'])) {
                                $linkUrl = url($resource['url']);
                                $isActive = request()->is(ltrim($resource['url'], '/') . '*');
                            } elseif (
                                isset($resource['route']) &&
                                \Illuminate\Support\Facades\Route::has($resource['route'])
                            ) {
                                $linkUrl = route($resource['route']);
                                $isActive = request()->routeIs($resource['route'] . '*');
                            } else {
                                $linkUrl = route($dashboardRoute::name('resources.index'), [
                                    'resource' => (string) $key,
                                ]);
                                $isActive = request()->is('*resources/' . $key . '*');
                            }

                            $target = $resource['target'] ?? '_self';
                        @endphp

                        <a href="{{ $linkUrl }}" target="{{ $target }}"
                            class="sidebar-link {{ $isActive ? 'active' : '' }}">
                            @if (isset($resource['icon']))
                                <span
                                    style="width: 18px; height: 18px; display: inline-flex; align-items: center; justify-content: center; margin-right: 6px;">
                                    {!! $resource['icon'] !!}
                                </span>
                            @else
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            @endif
                            <span>{{ $resource['title'] ?? ucfirst($key) }}</span>
                        </a>
                    @endforeach
                </div>
            @endforeach
        @endif
    </nav>
</aside>


