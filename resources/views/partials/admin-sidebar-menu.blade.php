@php
    $sidebarIcons = [
        'dashboard' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />',
        'car' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />',
        'brand' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008ZM17.25 15h.008v.008h-.008V15Z" />',
        'model' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h7.5M8.25 12h7.5m-7.5 5.25h4.5M3.75 6.75h.008v.008H3.75V6.75Zm0 5.25h.008v.008H3.75V12Zm0 5.25h.008v.008H3.75v-.008ZM6 3.75h12A2.25 2.25 0 0 1 20.25 6v12A2.25 2.25 0 0 1 18 20.25H6A2.25 2.25 0 0 1 3.75 18V6A2.25 2.25 0 0 1 6 3.75Z" />',
        'inventory' => '<path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />',
        'inventory_check' => '<path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3 1.5 1.5 3-3.75" />',
        'customers' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />',
        'orders' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />',
        'calendar' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />',
        'news' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" />',
        'promotion' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />',
        'review' => '<path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />',
        'live' => '<path stroke-linecap="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z" />',
        'support' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 0 1-.923 1.785A5.969 5.969 0 0 0 6 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337Z" />',
        'reports' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" />',
        'system' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.075.04.148.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a7.723 7.723 0 0 1 0 .255c-.007.378.138.751.431.992l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.02-.397-1.11-.94l-.213-1.281c-.063-.374-.313-.686-.645-.87a6.52 6.52 0 0 1-.22-.127c-.324-.196-.72-.257-1.075-.124l-1.217.456a1.125 1.125 0 0 1-1.37-.49l-1.296-2.247a1.125 1.125 0 0 1 .26-1.431l1.003-.827c.293-.24.438-.613.431-.992a6.932 6.932 0 0 1 0-.255c.007-.378-.138-.751-.431-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.298-2.247a1.125 1.125 0 0 1 1.369-.491l1.217.456c.355.133.75.072 1.076-.124.072-.044.145-.086.22-.128.331-.183.581-.495.644-.869l.213-1.281Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />',
    ];

    $sidebarIcon = function (string $name, string $class = 'sidebar-icon') use ($sidebarIcons): string {
        $paths = $sidebarIcons[$name] ?? $sidebarIcons['dashboard'];

        return '<svg class="' . e($class) . '" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">' . $paths . '</svg>';
    };

    $adminSidebarGroups = [
        [
            'label' => 'Bảng điều khiển',
            'icon' => 'dashboard',
            'items' => [
                ['label' => 'Bảng điều khiển', 'route' => 'admin.dashboard', 'active' => ['admin.dashboard'], 'icon' => 'dashboard'],
            ],
        ],
        [
            'label' => 'Quản lý xe',
            'icon' => 'car',
            'items' => [
                ['label' => 'Quản lý kho xe', 'route' => 'admin.cars.index', 'active' => ['admin.cars.*'], 'icon' => 'car'],
                ['label' => 'Quản lý hãng xe', 'route' => 'admin.brands.index', 'active' => ['admin.brands.*'], 'icon' => 'brand'],
                ['label' => 'Quản lý dòng xe', 'route' => 'admin.car-models.index', 'active' => ['admin.car-models.*'], 'icon' => 'model'],
                ['label' => 'Tồn kho', 'route' => 'admin.reports.inventory', 'active' => ['admin.reports.inventory'], 'icon' => 'inventory'],
                ['label' => 'Kiểm tra tồn', 'route' => 'admin.reports.inventory_check', 'active' => ['admin.reports.inventory_check', 'admin.reports.inventory_log'], 'icon' => 'inventory_check'],
            ],
        ],
        [
            'label' => 'Bán hàng',
            'icon' => 'orders',
            'items' => [
                ['label' => 'Khách hàng', 'route' => 'admin.reports.customers', 'active' => ['admin.reports.customers'], 'icon' => 'customers'],
                ['label' => 'Quản lý Đơn hàng', 'route' => 'admin.orders.index', 'active' => ['admin.orders.*'], 'icon' => 'orders'],
                ['label' => 'Đặt lịch lái thử', 'route' => 'admin.test_drives.index', 'active' => ['admin.test_drives.*'], 'icon' => 'calendar'],
            ],
        ],
        [
            'label' => 'Marketing',
            'icon' => 'promotion',
            'items' => [
                ['label' => 'Quản lý tin tức', 'route' => 'admin.news.index', 'active' => ['admin.news.*'], 'icon' => 'news'],
                ['label' => 'Khuyến mãi', 'route' => 'admin.promotions', 'active' => ['admin.promotions*'], 'icon' => 'promotion'],
                ['label' => 'Đánh giá', 'route' => 'admin.reports.reviews', 'active' => ['admin.reports.reviews'], 'icon' => 'review'],
                ['label' => 'Quản lý Livestream', 'route' => 'admin.live.index', 'active' => ['admin.live.*'], 'icon' => 'live', 'class' => 'sidebar-link-live'],
            ],
        ],
        [
            'label' => 'Hỗ trợ khách hàng',
            'icon' => 'support',
            'items' => [
                ['label' => 'Quản lý Hỗ trợ', 'route' => 'admin.tickets.index', 'active' => ['admin.tickets.*'], 'icon' => 'support'],
            ],
        ],
        [
            'label' => 'Báo cáo & Phân tích',
            'icon' => 'reports',
            'items' => [
                ['label' => 'Doanh số', 'route' => 'admin.reports.sales', 'active' => ['admin.reports.sales'], 'icon' => 'reports'],
            ],
        ],
        [
            'label' => 'Hệ thống',
            'icon' => 'system',
            'items' => [
                ['label' => 'Quản lý người dùng', 'route' => 'admin.users.index', 'active' => ['admin.users.*'], 'icon' => 'customers'],
            ],
        ],
    ];
@endphp

<nav class="sidebar-nav" aria-label="Menu quản trị">
    @foreach ($adminSidebarGroups as $group)
        @php
            $isGroupActive = collect($group['items'])->contains(fn ($item) => request()->routeIs(...$item['active']));
            $groupId = 'admin-menu-group-' . $loop->index;
        @endphp

        <div class="sidebar-group {{ $isGroupActive ? 'is-open' : '' }}" data-sidebar-group>
            <button type="button"
                class="sidebar-link sidebar-parent {{ $isGroupActive ? 'active' : '' }}"
                data-sidebar-group-toggle
                aria-expanded="{{ $isGroupActive ? 'true' : 'false' }}"
                aria-controls="{{ $groupId }}">
                {!! $sidebarIcon($group['icon']) !!}
                <span class="sidebar-label">{{ $group['label'] }}</span>
                <svg class="sidebar-caret" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                </svg>
            </button>

            <div class="sidebar-group-children" id="{{ $groupId }}" aria-hidden="{{ $isGroupActive ? 'false' : 'true' }}">
                @foreach ($group['items'] as $item)
                    @php
                        $isItemActive = request()->routeIs(...$item['active']);
                        $itemClasses = trim('sidebar-link sidebar-child-link ' . ($item['class'] ?? '') . ' ' . ($isItemActive ? 'active' : ''));
                    @endphp

                    <a href="{{ route($item['route']) }}" class="{{ $itemClasses }}">
                        {!! $sidebarIcon($item['icon']) !!}
                        <span class="sidebar-label">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endforeach
</nav>
