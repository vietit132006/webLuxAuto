@php
    $sidebarIcons = [
        'dashboard' => '<rect width="7" height="9" x="3" y="3" rx="1" /><rect width="7" height="5" x="14" y="3" rx="1" /><rect width="7" height="9" x="14" y="12" rx="1" /><rect width="7" height="5" x="3" y="16" rx="1" />',
        'car' => '<path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9L18.4 6c-.3-.6-.9-1-1.6-1H7.2c-.7 0-1.3.4-1.6 1L3.5 11.1C2.7 11.3 2 12.1 2 13v3c0 .6.4 1 1 1h2" /><circle cx="7" cy="17" r="2" /><path d="M9 17h6" /><circle cx="17" cy="17" r="2" />',
        'building' => '<path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z" /><path d="M6 12H4a2 2 0 0 0-2 2v8h20v-8a2 2 0 0 0-2-2h-2" /><path d="M10 6h4" /><path d="M10 10h4" /><path d="M10 14h4" /><path d="M10 18h4" />',
        'list_tree' => '<path d="M21 6H8" /><path d="M21 12h-8" /><path d="M21 18h-8" /><path d="M3 6h1c2 0 3 1 3 3v6c0 2 1 3 3 3h1" />',
        'boxes' => '<path d="m7.5 4.27 4.5 2.6 4.5-2.6" /><path d="M3 8.54 12 14l9-5.46" /><path d="M12 14v8" /><path d="M3 8.54v6.92a2 2 0 0 0 1 1.73l7 4.04a2 2 0 0 0 2 0l7-4.04a2 2 0 0 0 1-1.73V8.54a2 2 0 0 0-1-1.73l-7-4.04a2 2 0 0 0-2 0l-7 4.04a2 2 0 0 0-1 1.73Z" />',
        'clipboard_check' => '<rect width="8" height="4" x="8" y="2" rx="1" /><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" /><path d="m9 14 2 2 4-4" />',
        'history' => '<path d="M3 12a9 9 0 1 0 3-6.7" /><path d="M3 3v6h6" /><path d="M12 7v5l3 2" />',
        'shopping_cart' => '<circle cx="8" cy="21" r="1" /><circle cx="19" cy="21" r="1" /><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h8.36a2 2 0 0 0 1.95-1.57L21 4H5.12" />',
        'file_text' => '<path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z" /><path d="M14 2v4a2 2 0 0 0 2 2h4" /><path d="M10 9H8" /><path d="M16 13H8" /><path d="M16 17H8" />',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M22 21v-2a4 4 0 0 0-3-3.87" /><path d="M16 3.13a4 4 0 0 1 0 7.75" />',
        'calendar' => '<path d="M8 2v4" /><path d="M16 2v4" /><rect width="18" height="18" x="3" y="4" rx="2" /><path d="M3 10h18" />',
        'newspaper' => '<path d="M4 22h16a2 2 0 0 0 2-2V4H6a2 2 0 0 0-2 2v16Z" /><path d="M16 2v4" /><path d="M8 10h8" /><path d="M8 14h8" /><path d="M8 18h5" />',
        'megaphone' => '<path d="m3 11 18-5v12L3 14v-3Z" /><path d="M11.6 16.8a3 3 0 0 1-5.8-1.6" />',
        'tag' => '<path d="M12.59 2.59A2 2 0 0 0 11.17 2H4a2 2 0 0 0-2 2v7.17a2 2 0 0 0 .59 1.42l8.7 8.7a2.43 2.43 0 0 0 3.42 0l6.58-6.58a2.43 2.43 0 0 0 0-3.42l-8.7-8.7Z" /><path d="M7.5 7.5h.01" />',
        'star' => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />',
        'video' => '<path d="m16 13 5.22 3.48a.5.5 0 0 0 .78-.42V7.94a.5.5 0 0 0-.78-.42L16 11" /><rect x="2" y="6" width="14" height="12" rx="2" />',
        'headset' => '<path d="M3 11a9 9 0 0 1 18 0" /><path d="M21 12v4a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3Z" /><path d="M3 12v4a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3Z" /><path d="M16 18v1a2 2 0 0 1-2 2h-2" />',
        'bar_chart' => '<path d="M3 3v18h18" /><path d="M18 17V9" /><path d="M13 17V5" /><path d="M8 17v-3" />',
        'settings' => '<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.38a2 2 0 0 0-.73-2.73l-.15-.09a2 2 0 0 1-1-1.74v-.51a2 2 0 0 1 1-1.72l.15-.1a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2Z" /><circle cx="12" cy="12" r="3" />',
    ];

    $sidebarIcon = function (string $name, string $class = 'sidebar-icon') use ($sidebarIcons): string {
        $paths = $sidebarIcons[$name] ?? $sidebarIcons['dashboard'];

        return '<svg class="' . e($class) . '" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $paths . '</svg>';
    };

    $adminSidebarGroups = [
        [
            'label' => 'Bảng điều khiển',
            'icon' => 'dashboard',
            'route' => 'admin.dashboard',
            'active' => ['admin.dashboard'],
            'permission' => 'dashboard.view',
        ],
        [
            'label' => 'Quản lý xe',
            'icon' => 'car',
            'items' => [
                ['label' => 'Quản lý kho xe', 'route' => 'admin.cars.index', 'active' => ['admin.cars.*'], 'icon' => 'car', 'permission' => 'cars.view'],
                ['label' => 'Hãng xe', 'route' => 'admin.brands.index', 'active' => ['admin.brands.*'], 'icon' => 'building', 'permission' => 'cars.view'],
                ['label' => 'Dòng xe', 'route' => 'admin.car-models.index', 'active' => ['admin.car-models.*'], 'icon' => 'list_tree', 'permission' => 'cars.view'],
                ['label' => 'Tồn kho', 'route' => 'admin.reports.inventory', 'active' => ['admin.reports.inventory'], 'icon' => 'boxes', 'permission' => 'inventory.view'],
                ['label' => 'Kiểm tra tồn', 'route' => 'admin.reports.inventory_check', 'active' => ['admin.reports.inventory_check', 'admin.reports.inventory_log'], 'icon' => 'clipboard_check', 'permission' => 'inventory.adjust'],
                ['label' => 'Lịch sử tồn kho', 'route' => 'admin.stock-movements.index', 'active' => ['admin.stock-movements.*'], 'icon' => 'history', 'permission' => 'inventory.history'],
            ],
        ],
        [
            'label' => 'Bán hàng',
            'icon' => 'shopping_cart',
            'items' => [
                ['label' => 'Khách hàng', 'route' => 'admin.customers.index', 'active' => ['admin.customers.*'], 'icon' => 'users', 'permission' => 'customers.view'],
                ['label' => 'Báo giá', 'route' => 'admin.quotes.index', 'active' => ['admin.quotes.*'], 'icon' => 'file_text', 'permission' => 'quotes.view'],
                ['label' => 'Đơn hàng', 'route' => 'admin.orders.index', 'active' => ['admin.orders.*'], 'icon' => 'shopping_cart', 'permission' => 'orders.view'],
                ['label' => 'Lái thử', 'route' => 'admin.test_drives.index', 'active' => ['admin.test_drives.*'], 'icon' => 'calendar', 'permission' => 'test_drives.view'],
            ],
        ],
        [
            'label' => 'Marketing',
            'icon' => 'megaphone',
            'items' => [
                ['label' => 'Tin tức', 'route' => 'admin.news.index', 'active' => ['admin.news.*'], 'icon' => 'newspaper', 'permission' => 'news.view'],
                ['label' => 'Khuyến mãi', 'route' => 'admin.promotions', 'active' => ['admin.promotions*'], 'icon' => 'tag', 'permission' => 'promotions.view'],
                ['label' => 'Đánh giá', 'route' => 'admin.reports.reviews', 'active' => ['admin.reports.reviews'], 'icon' => 'star', 'permission' => 'reviews.view'],
                ['label' => 'Livestream', 'route' => 'admin.live.index', 'active' => ['admin.live.*'], 'icon' => 'video', 'class' => 'sidebar-link-live', 'permission' => 'live.view'],
            ],
        ],
        [
            'label' => 'Hỗ trợ khách hàng',
            'icon' => 'headset',
            'items' => [
                ['label' => 'Hỗ trợ', 'route' => 'admin.tickets.index', 'active' => ['admin.tickets.*'], 'icon' => 'headset', 'permission' => 'tickets.view'],
            ],
        ],
        [
            'label' => 'Báo cáo & Phân tích',
            'icon' => 'bar_chart',
            'items' => [
                ['label' => 'Doanh số', 'route' => 'admin.reports.sales', 'active' => ['admin.reports.sales', 'admin.reports.sales.export'], 'icon' => 'bar_chart', 'permission' => 'reports.view'],
                ['label' => 'Tồn kho', 'route' => 'admin.reports.inventory', 'active' => ['admin.reports.inventory', 'admin.reports.inventory.export'], 'icon' => 'boxes', 'permission' => ['reports.view', 'inventory.view']],
                ['label' => 'Giữ chỗ xe', 'route' => 'admin.reports.reservations', 'active' => ['admin.reports.reservations', 'admin.reports.reservations.export'], 'icon' => 'clipboard_check', 'permission' => ['reports.view', 'inventory.view']],
                ['label' => 'Giao xe', 'route' => 'admin.reports.deliveries', 'active' => ['admin.reports.deliveries', 'admin.reports.deliveries.export'], 'icon' => 'calendar', 'permission' => ['reports.view', 'inventory.view']],
                ['label' => 'Khách hàng', 'route' => 'admin.reports.customers', 'active' => ['admin.reports.customers', 'admin.reports.customers.export'], 'icon' => 'users', 'permission' => 'reports.view'],
                ['label' => 'Nhân viên sale', 'route' => 'admin.reports.staff', 'active' => ['admin.reports.staff', 'admin.reports.staff.export'], 'icon' => 'users', 'permission' => 'reports.view'],
                ['label' => 'Chuyển đổi', 'route' => 'admin.reports.conversion', 'active' => ['admin.reports.conversion'], 'icon' => 'bar_chart', 'permission' => 'reports.view'],
            ],
        ],
        [
            'label' => 'Hệ thống',
            'icon' => 'settings',
            'items' => [
                ['label' => 'Người dùng', 'route' => 'admin.users.index', 'active' => ['admin.users.*'], 'icon' => 'users', 'permission' => 'users.view'],
                ['label' => 'Vai trò', 'route' => 'admin.roles.index', 'active' => ['admin.roles.*'], 'icon' => 'settings', 'permission' => 'roles.view'],
            ],
        ],
    ];

    $canSeePermission = function (string|array|null $permission): bool {
        if (!$permission) {
            return true;
        }

        $user = auth()->user();

        return $user ? $user->canAny((array) $permission) : false;
    };

    $isVisibleSidebarItem = fn (array $item): bool => $canSeePermission($item['permission'] ?? null);
@endphp

<nav class="sidebar-nav" aria-label="Menu quản trị">
    @foreach ($adminSidebarGroups as $group)
        @php
            $isDirectLink = isset($group['route']);
            $visibleItems = $isDirectLink ? collect() : collect($group['items'])->filter($isVisibleSidebarItem)->values();
            $isGroupVisible = $isDirectLink ? $isVisibleSidebarItem($group) : $visibleItems->isNotEmpty();

            if (!$isGroupVisible) {
                continue;
            }

            $isGroupActive = $isDirectLink
                ? request()->routeIs(...$group['active'])
                : $visibleItems->contains(fn ($item) => request()->routeIs(...$item['active']));
            $groupId = 'admin-menu-group-' . $loop->index;
        @endphp

        @if ($isDirectLink)
            <a href="{{ route($group['route']) }}" class="sidebar-link {{ $isGroupActive ? 'active' : '' }}">
                {!! $sidebarIcon($group['icon']) !!}
                <span class="sidebar-label">{{ $group['label'] }}</span>
            </a>
        @else
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
                    @foreach ($visibleItems as $item)
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
        @endif
    @endforeach
</nav>
