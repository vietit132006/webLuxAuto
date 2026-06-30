 <!DOCTYPE html>
 <html lang="vi">

 <head>
     <meta charset="utf-8">
     <meta name="viewport" content="width=device-width, initial-scale=1">
     <meta name="csrf-token" content="{{ csrf_token() }}">
     <title>@yield('title', 'Admin Dashboard') — Lux Auto</title>

     @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
         @vite(['resources/css/app.css', 'resources/js/app.js'])
     @endif

     @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
         @vite('resources/css/layout-admin.css')
     @endif
     @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
         @vite('resources/css/partial-saved-login-switcher.css')
     @endif
     @stack('styles')
 </head>

 <body>
     @php
         $adminNotificationUnreadTotal = 0;
         $adminNotificationUnreadByModule = [];
         $adminLatestNotifications = collect();
         $adminNotificationsEnabled = false;
         $adminNotificationUser = auth()->user();

         try {
             $adminNotificationsEnabled = $adminNotificationUser
                 && $adminNotificationUser->can('notifications.view');
         } catch (\Throwable) {
             $adminNotificationsEnabled = false;
         }

         if ($adminNotificationsEnabled) {
             $adminNotificationService = app(\App\Services\AdminNotificationService::class);
             $adminNotificationUnreadTotal = $adminNotificationService->unreadCount($adminNotificationUser);
             $adminNotificationUnreadByModule = $adminNotificationService->unreadCountByModule($adminNotificationUser);
             $adminLatestNotifications = $adminNotificationService->latestUnread($adminNotificationUser, 10);
         }
     @endphp

     <aside class="admin-sidebar" id="admin-sidebar">
         <a href="{{ route('home') }}" class="sidebar-brand" target="_blank" title="Mở trang khách hàng">
             Lux <span>Auto</span>
         </a>
         @include('partials.admin-sidebar-menu')
     </aside>
     <button type="button" class="mobile-menu-overlay" id="admin-menu-overlay" aria-label="Đóng menu"></button>

     <div class="admin-wrapper">
         <header class="admin-topbar">
             <button type="button" class="mobile-menu-toggle" id="admin-menu-toggle" aria-label="Mở menu quản trị"
                 aria-controls="admin-sidebar" aria-expanded="false">
                 <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                     <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
                 </svg>
             </button>

             <div class="layout-admin-inline-1"></div>

             <div class="admin-topbar-info">
                 @if ($isAccountSwitching && $accountSwitcher)
                     <div class="account-switch-notice">
                         <span>Đang xem: <strong>{{ auth()->user()->name ?? 'Tài khoản' }}</strong></span>
                         <form method="POST" action="{{ route('account-switch.restore') }}">
                             @csrf
                             <button type="submit" class="account-switch-restore">Quay lại
                                 {{ $accountSwitcher->name }}</button>
                         </form>
                     </div>
                 @endif

                 @include('partials.admin-notification-bell')

                 <div class="admin-account-menu" data-account-menu>
                     <button type="button" class="admin-account-trigger" data-account-menu-trigger aria-haspopup="true"
                         aria-expanded="false">
                         <span class="admin-account-avatar">
                             {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(auth()->user()->name ?? 'A', 0, 1)) }}
                         </span>
                         <span class="admin-account-summary">
                             <strong>{{ auth()->user()->name ?? 'Admin' }}</strong>
                             <span>{{ auth()->user()->email ?? 'admin@luxauto.local' }}</span>
                         </span>
                         <svg class="admin-account-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             stroke-width="2">
                             <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                         </svg>
                     </button>

                     <div class="admin-account-dropdown" role="menu">
                         <div class="admin-account-card">
                             <span class="admin-account-avatar">
                                 {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(auth()->user()->name ?? 'A', 0, 1)) }}
                             </span>
                             <div>
                                 <strong>{{ auth()->user()->name ?? 'Admin' }}</strong>
                                 <span>{{ auth()->user()->email ?? '' }}</span>
                             </div>
                         </div>

                         <a href="{{ route('profile.index') }}" class="admin-account-item" role="menuitem">
                             <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                 <path stroke-linecap="round" stroke-linejoin="round"
                                     d="M15.75 7.5a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0" />
                             </svg>
                             Thông tin tài khoản
                         </a>
                         <button type="button" class="admin-account-item" data-open-account-switcher role="menuitem">
                             <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                 <path stroke-linecap="round" stroke-linejoin="round"
                                     d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h15M16.5 3 21 7.5m0 0L16.5 12M21 7.5H6" />
                             </svg>
                             Chuyển đổi tài khoản
                         </button>
                         <div class="admin-account-divider"></div>
                         <a href="{{ route('logout') }}" class="admin-account-item is-danger" role="menuitem">
                             <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                 <path stroke-linecap="round" stroke-linejoin="round"
                                     d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3-6 3 3m0 0-3 3m3-3H9" />
                             </svg>
                             Đăng xuất
                         </a>
                     </div>
                 </div>
             </div>
         </header>

         <main class="admin-main">
             @yield('content')
         </main>
     </div>

     @include('partials.saved-login-switcher')

     <script>
         (() => {
             const body = document.body;
             const toggleButton = document.getElementById('admin-menu-toggle');
             const overlay = document.getElementById('admin-menu-overlay');
             const sidebar = document.getElementById('admin-sidebar');

             if (!toggleButton || !overlay || !sidebar) {
                 return;
             }

             const setMenuOpen = (isOpen) => {
                 body.classList.toggle('sidebar-open', isOpen);
                 toggleButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
             };

             toggleButton.addEventListener('click', () => {
                 setMenuOpen(!body.classList.contains('sidebar-open'));
             });

             overlay.addEventListener('click', () => setMenuOpen(false));

             const syncSidebarGroup = (group, isOpen) => {
                 const trigger = group.querySelector('[data-sidebar-group-toggle]');
                 const panel = trigger ?
                     document.getElementById(trigger.getAttribute('aria-controls')) :
                     group.querySelector('.sidebar-group-children');

                 if (!trigger) {
                     return;
                 }

                 group.classList.toggle('is-open', isOpen);
                 trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

                 if (panel) {
                     panel.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
                     panel.querySelectorAll('a').forEach((link) => {
                         link.tabIndex = isOpen ? 0 : -1;
                     });
                 }
             };

             sidebar.querySelectorAll('[data-sidebar-group]').forEach((group) => {
                 syncSidebarGroup(group, group.classList.contains('is-open'));

                 const trigger = group.querySelector('[data-sidebar-group-toggle]');

                 trigger.addEventListener('click', () => {
                     const isOpen = !group.classList.contains('is-open');

                     syncSidebarGroup(group, isOpen);
                 });
             });

             sidebar.querySelectorAll('a').forEach((link) => {
                 link.addEventListener('click', () => setMenuOpen(false));
             });

             const dropdownMenus = [
                 {
                     menu: document.querySelector('[data-account-menu]'),
                     trigger: document.querySelector('[data-account-menu-trigger]'),
                 },
                 {
                     menu: document.querySelector('[data-admin-notifications]'),
                     trigger: document.querySelector('[data-admin-notification-trigger]'),
                 },
             ].filter((item) => item.menu && item.trigger);

             const closeDropdowns = (except = null) => {
                 dropdownMenus.forEach(({ menu, trigger }) => {
                     if (menu === except) {
                         return;
                     }

                     menu.classList.remove('is-open');
                     trigger.setAttribute('aria-expanded', 'false');
                 });
             };

             dropdownMenus.forEach(({ menu, trigger }) => {
                 trigger.addEventListener('click', (event) => {
                     event.stopPropagation();
                     const willOpen = !menu.classList.contains('is-open');

                     closeDropdowns(menu);
                     menu.classList.toggle('is-open', willOpen);
                     trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                 });

                 menu.addEventListener('click', (event) => {
                     event.stopPropagation();
                 });
             });

             document.addEventListener('click', () => closeDropdowns());

             const notificationRoot = document.querySelector('[data-admin-notifications]');

             if (notificationRoot) {
                 const csrfToken = notificationRoot.dataset.csrfToken;
                 const summaryUrl = notificationRoot.dataset.summaryUrl;
                 const latestRoot = notificationRoot.querySelector('[data-notification-latest]');
                 const headerCount = notificationRoot.querySelector('[data-notification-header-count]');
                 const readAllForm = notificationRoot.querySelector('[data-notification-read-all-form]');

                 const formatCount = (value) => {
                     const count = Number.parseInt(value || 0, 10);

                     return count > 99 ? '99+' : String(Math.max(0, count));
                 };

                 const setCountBadge = (element, count) => {
                     if (!element) {
                         return;
                     }

                     element.textContent = formatCount(count);
                     element.classList.toggle('is-hidden', Number(count) <= 0);
                 };

                 const updateModuleBadges = (modules = {}) => {
                     document.querySelectorAll('[data-notification-module-badge]').forEach((badge) => {
                         const moduleNames = String(badge.dataset.notificationModules || '')
                             .split(',')
                             .map((module) => module.trim())
                             .filter(Boolean);
                         const count = moduleNames.reduce((sum, module) => sum + Number(modules[module] || 0), 0);

                         setCountBadge(badge, count);
                     });
                 };

                 const appendText = (parent, text) => {
                     parent.appendChild(document.createTextNode(text || ''));
                 };

                 const renderLatest = (items = []) => {
                     if (!latestRoot) {
                         return;
                     }

                     latestRoot.replaceChildren();

                     if (!items.length) {
                         const empty = document.createElement('div');
                         empty.className = 'admin-notification-empty';
                         empty.dataset.notificationEmpty = '';
                         appendText(empty, 'Chua co thong bao moi.');
                         latestRoot.appendChild(empty);

                         return;
                     }

                     items.forEach((item) => {
                         const form = document.createElement('form');
                         form.method = 'POST';
                         form.action = item.read_url;
                         form.className = 'admin-notification-row-form';

                         const token = document.createElement('input');
                         token.type = 'hidden';
                         token.name = '_token';
                         token.value = csrfToken;
                         form.appendChild(token);

                         const button = document.createElement('button');
                         button.type = 'submit';
                         button.className = `admin-notification-item ${item.priority_class || ''}`;
                         button.setAttribute('role', 'menuitem');

                         const main = document.createElement('span');
                         main.className = 'admin-notification-item-main';

                         const title = document.createElement('span');
                         title.className = 'admin-notification-title';
                         appendText(title, item.title);

                         const meta = document.createElement('span');
                         meta.className = 'admin-notification-meta';

                         const module = document.createElement('span');
                         appendText(module, item.module_label);

                         const time = document.createElement('span');
                         appendText(time, item.created_at_human);

                         meta.append(module, time);
                         main.append(title, meta);

                         const priority = document.createElement('span');
                         priority.className = `admin-notification-priority ${item.priority_class || ''}`;
                         appendText(priority, item.priority_label);

                         button.append(main, priority);
                         form.appendChild(button);
                         latestRoot.appendChild(form);
                     });
                 };

                 const updateNotificationSummary = (payload) => {
                     const total = Number(payload.total || 0);

                     document.querySelectorAll('[data-notification-total]').forEach((badge) => setCountBadge(badge, total));

                     if (headerCount) {
                         headerCount.textContent = `${new Intl.NumberFormat('vi-VN').format(total)} chua doc`;
                     }

                     updateModuleBadges(payload.modules || {});
                     renderLatest(payload.latest || []);
                 };

                 const refreshNotifications = async () => {
                     if (!summaryUrl) {
                         return;
                     }

                     try {
                         const response = await fetch(summaryUrl, {
                             headers: {
                                 Accept: 'application/json',
                             },
                         });

                         if (response.ok) {
                             updateNotificationSummary(await response.json());
                         }
                     } catch (error) {
                         // Polling is a progressive enhancement; keep the server-rendered state on failure.
                     }
                 };

                 if (readAllForm) {
                     readAllForm.addEventListener('submit', async (event) => {
                         event.preventDefault();

                         try {
                             const response = await fetch(readAllForm.action, {
                                 method: 'POST',
                                 headers: {
                                     Accept: 'application/json',
                                     'X-CSRF-TOKEN': csrfToken,
                                 },
                             });

                             if (response.ok) {
                                 updateNotificationSummary(await response.json());
                             }
                         } catch (error) {
                             readAllForm.submit();
                         }
                     });
                 }

                 window.setInterval(refreshNotifications, 45000);
             }

             window.addEventListener('keydown', (event) => {
                 if (event.key === 'Escape') {
                     setMenuOpen(false);
                     closeDropdowns();
                 }
             });

             window.addEventListener('resize', () => {
                 if (window.innerWidth > 768) {
                     setMenuOpen(false);
                 }
             });
         })();
     </script>

     @stack('scripts')
 </body>

 </html>
