
<div class="lsa-toast" data-lsa-toast role="status" aria-live="polite"></div>

@auth
    <div class="lsa-dialog" data-save-account-prompt hidden>
        <button type="button" class="lsa-backdrop" data-lsa-close aria-label="Đóng"></button>
        <section class="lsa-panel lsa-panel--compact" role="dialog" aria-modal="true" aria-labelledby="lsa-save-title">
            <div class="lsa-header">
                <div>
                    <h2 class="lsa-title" id="lsa-save-title">Lưu tài khoản trên thiết bị này?</h2>
                    <p class="lsa-subtitle">Lần sau bạn có thể đăng nhập nhanh mà không cần nhập mật khẩu.</p>
                </div>
                <button type="button" class="lsa-close" data-lsa-close aria-label="Đóng">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="lsa-body">
                <p class="lsa-copy">Bạn có muốn lưu tài khoản này trên thiết bị này không?</p>
            </div>
            <div class="lsa-actions">
                <button type="button" class="lsa-btn" data-save-account-dismiss>Không lưu</button>
                <button type="button" class="lsa-btn lsa-btn--primary" data-save-account-confirm data-lsa-primary>
                    Lưu tài khoản
                </button>
            </div>
        </section>
    </div>
@endauth

<div class="lsa-dialog" data-account-switch-modal hidden>
    <button type="button" class="lsa-backdrop" data-lsa-close aria-label="Đóng"></button>
    <section class="lsa-panel" role="dialog" aria-modal="true" aria-labelledby="lsa-switch-title">
        <div class="lsa-header">
            <div>
                <h2 class="lsa-title" id="lsa-switch-title">Chuyển đổi tài khoản</h2>
                <p class="lsa-subtitle">Các tài khoản đã lưu trên thiết bị hiện tại.</p>
            </div>
            <button type="button" class="lsa-close" data-lsa-close aria-label="Đóng">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="lsa-body">
            <div class="lsa-list" data-saved-account-list></div>
            <div class="lsa-empty" data-saved-account-empty hidden>
                Chưa có tài khoản nào được lưu trên thiết bị này.
            </div>
        </div>
    </section>
</div>

@php
    $savedLoginCurrentUser = auth()->check() ? [
        'user_id' => auth()->user()->getKey(),
        'name' => auth()->user()->name,
        'email' => auth()->user()->email,
        'role' => auth()->user()->role,
        'avatar_url' => null,
    ] : null;
@endphp

<script>
    (() => {
        if (window.__luxSavedLoginSwitcherReady) {
            return;
        }

        window.__luxSavedLoginSwitcherReady = true;

        const config = {
            csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || @json(csrf_token()),
            currentUser: @json($savedLoginCurrentUser),
            shouldPromptSave: @json((bool) session('show_save_login_prompt')),
            routes: {
                store: @json(route('saved-login-accounts.store')),
                login: @json(route('saved-login-accounts.login')),
                destroy: @json(route('saved-login-accounts.destroy')),
            },
        };

        const storageKey = 'lux_saved_login_accounts';
        const deviceKey = 'lux_saved_login_device_id';
        const toast = document.querySelector('[data-lsa-toast]');
        const savePrompt = document.querySelector('[data-save-account-prompt]');
        const switchModal = document.querySelector('[data-account-switch-modal]');
        const accountList = document.querySelector('[data-saved-account-list]');
        const emptyState = document.querySelector('[data-saved-account-empty]');
        let toastTimer = null;

        const safeLocalStorage = {
            get(key) {
                try {
                    return window.localStorage.getItem(key);
                } catch (error) {
                    return null;
                }
            },
            set(key, value) {
                try {
                    window.localStorage.setItem(key, value);
                    return true;
                } catch (error) {
                    return false;
                }
            },
        };

        const getDeviceId = () => {
            const existing = safeLocalStorage.get(deviceKey);

            if (existing) {
                return existing;
            }

            const id = window.crypto?.randomUUID
                ? window.crypto.randomUUID()
                : `device-${Date.now()}-${Math.random().toString(16).slice(2)}`;

            safeLocalStorage.set(deviceKey, id);

            return id;
        };

        const deviceId = getDeviceId();

        const getDeviceName = () => {
            const ua = navigator.userAgent || '';
            let browser = 'Browser';
            let os = 'Thiết bị';

            if (/Edg\//.test(ua)) browser = 'Edge';
            else if (/OPR\//.test(ua)) browser = 'Opera';
            else if (/Chrome|CriOS/.test(ua)) browser = 'Chrome';
            else if (/Firefox/.test(ua)) browser = 'Firefox';
            else if (/Safari/.test(ua)) browser = 'Safari';

            if (/Windows/.test(ua)) os = 'Windows';
            else if (/Mac OS X/.test(ua)) os = 'macOS';
            else if (/Android/.test(ua)) os = 'Android';
            else if (/iPhone/.test(ua)) os = 'iPhone';
            else if (/iPad/.test(ua)) os = 'iPad';
            else if (/Linux/.test(ua)) os = 'Linux';

            return `${browser} ${os}`;
        };

        const deviceName = getDeviceName();

        const getAccounts = () => {
            const raw = safeLocalStorage.get(storageKey);

            if (!raw) {
                return [];
            }

            try {
                const accounts = JSON.parse(raw);
                return Array.isArray(accounts) ? accounts : [];
            } catch (error) {
                return [];
            }
        };

        const setAccounts = (accounts) => {
            safeLocalStorage.set(storageKey, JSON.stringify(accounts));
            updateLaunchers();
        };

        const accountsForDevice = () => getAccounts()
            .filter((account) => account && account.token && account.device_id === deviceId);

        const upsertAccount = (account) => {
            const normalized = {
                ...account,
                device_id: account.device_id || deviceId,
                device_name: account.device_name || deviceName,
                last_used_at: account.last_used_at || new Date().toISOString(),
            };

            const next = getAccounts()
                .filter((item) => !(String(item.user_id) === String(normalized.user_id) && item.device_id === normalized.device_id));

            next.unshift(normalized);
            setAccounts(next);
        };

        const removeAccount = (account) => {
            setAccounts(getAccounts().filter((item) => !(
                String(item.user_id) === String(account.user_id)
                && item.device_id === account.device_id
            )));
        };

        const hasCurrentSavedAccount = () => {
            if (!config.currentUser) {
                return false;
            }

            const now = Date.now();

            return accountsForDevice().some((account) => {
                const sameUser = String(account.user_id) === String(config.currentUser.user_id);
                const isFresh = !account.expires_at || new Date(account.expires_at).getTime() > now;

                return sameUser && isFresh;
            });
        };

        const initialsFor = (account) => {
            const source = (account.name || account.email || '?').trim();
            return Array.from(source)[0]?.toUpperCase() || '?';
        };

        const timeAgo = (value) => {
            if (!value) {
                return 'chưa sử dụng';
            }

            const timestamp = new Date(value).getTime();

            if (!Number.isFinite(timestamp)) {
                return 'chưa sử dụng';
            }

            const diffSeconds = Math.max(0, Math.floor((Date.now() - timestamp) / 1000));
            const units = [
                ['năm', 31536000],
                ['tháng', 2592000],
                ['ngày', 86400],
                ['giờ', 3600],
                ['phút', 60],
            ];

            for (const [label, seconds] of units) {
                const value = Math.floor(diffSeconds / seconds);

                if (value >= 1) {
                    return `${value} ${label} trước`;
                }
            }

            return 'vừa xong';
        };

        const createElement = (tag, className, text) => {
            const element = document.createElement(tag);

            if (className) {
                element.className = className;
            }

            if (text !== undefined) {
                element.textContent = text;
            }

            return element;
        };

        const showToast = (message, type = 'success') => {
            if (!toast) {
                return;
            }

            toast.textContent = message;
            toast.classList.toggle('lsa-toast--error', type === 'error');
            toast.classList.add('is-visible');
            clearTimeout(toastTimer);
            toastTimer = setTimeout(() => toast.classList.remove('is-visible'), 3400);
        };

        const setLoading = (button, loading, text = 'Đang xử lý...') => {
            if (!button) {
                return;
            }

            if (loading) {
                button.dataset.originalText = button.textContent;
                button.textContent = text;
                button.disabled = true;
                return;
            }

            button.textContent = button.dataset.originalText || button.textContent;
            button.disabled = false;
        };

        const requestJson = async (url, options = {}) => {
            const response = await fetch(url, {
                credentials: 'same-origin',
                ...options,
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': config.csrfToken,
                    ...(options.headers || {}),
                },
            });

            let data = {};

            try {
                data = await response.json();
            } catch (error) {
                data = {};
            }

            if (!response.ok) {
                const error = new Error(data.message || 'Không thể xử lý yêu cầu. Vui lòng thử lại.');
                error.data = data;
                error.status = response.status;
                throw error;
            }

            return data;
        };

        const closeAccountMenus = (except = null) => {
            document.querySelectorAll('[data-account-menu].is-open').forEach((menu) => {
                if (menu === except) {
                    return;
                }

                menu.classList.remove('is-open');
                menu.querySelector('[data-account-menu-trigger]')?.setAttribute('aria-expanded', 'false');
            });
        };

        const openDialog = (dialog) => {
            if (!dialog) {
                return;
            }

            dialog.hidden = false;
            document.body.style.overflow = 'hidden';
            requestAnimationFrame(() => {
                dialog.classList.add('is-open');
                dialog.querySelector('[data-lsa-primary], .lsa-close, button')?.focus({ preventScroll: true });
            });
        };

        const closeDialog = (dialog) => {
            if (!dialog) {
                return;
            }

            dialog.classList.remove('is-open');
            setTimeout(() => {
                dialog.hidden = true;

                if (!document.querySelector('.lsa-dialog.is-open')) {
                    document.body.style.overflow = '';
                }
            }, 180);
        };

        const renderAccounts = () => {
            if (!accountList || !emptyState) {
                return;
            }

            const accounts = accountsForDevice();
            accountList.replaceChildren();
            emptyState.hidden = accounts.length > 0;

            accounts.forEach((account) => {
                const card = createElement('div', 'lsa-account-card');
                card.setAttribute('role', 'button');
                card.setAttribute('tabindex', '0');

                const avatar = createElement('div', 'lsa-avatar');

                if (account.avatar_url) {
                    const image = document.createElement('img');
                    image.src = account.avatar_url;
                    image.alt = account.name || account.email || 'Avatar';
                    avatar.appendChild(image);
                } else {
                    avatar.textContent = initialsFor(account);
                }

                const main = createElement('div', 'lsa-account-main');
                main.appendChild(createElement('p', 'lsa-account-name', account.name || 'Tài khoản'));
                main.appendChild(createElement('p', 'lsa-account-email', account.email || ''));
                main.appendChild(createElement('p', 'lsa-account-meta', account.device_name || deviceName));
                main.appendChild(createElement('p', 'lsa-account-meta', `Đăng nhập ${timeAgo(account.last_used_at)}`));

                const actions = createElement('div', 'lsa-account-actions');
                const loginButton = createElement('button', 'lsa-btn lsa-btn--primary', 'Đăng nhập');
                loginButton.type = 'button';
                const deleteButton = createElement('button', 'lsa-btn lsa-btn--danger', 'Xóa');
                deleteButton.type = 'button';
                deleteButton.title = 'Xóa tài khoản đã lưu';

                const login = () => loginSavedAccount(account, loginButton);

                loginButton.addEventListener('click', (event) => {
                    event.stopPropagation();
                    login();
                });

                deleteButton.addEventListener('click', (event) => {
                    event.stopPropagation();
                    deleteSavedAccount(account, deleteButton);
                });

                card.addEventListener('click', login);
                card.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        login();
                    }
                });

                actions.append(loginButton, deleteButton);
                card.append(avatar, main, actions);
                accountList.appendChild(card);
            });
        };

        const openSwitchModal = () => {
            renderAccounts();
            openDialog(switchModal);
        };

        const saveCurrentAccount = async (button) => {
            if (!config.currentUser) {
                return;
            }

            setLoading(button, true);

            try {
                const data = await requestJson(config.routes.store, {
                    method: 'POST',
                    body: JSON.stringify({
                        device_id: deviceId,
                        device_name: deviceName,
                    }),
                });

                upsertAccount(data.account);
                closeDialog(savePrompt);
                showToast(data.message || 'Đã lưu tài khoản trên thiết bị này.');
            } catch (error) {
                showToast(error.message, 'error');
            } finally {
                setLoading(button, false);
            }
        };

        const loginSavedAccount = async (account, button) => {
            setLoading(button, true, 'Đang đăng nhập...');

            try {
                const data = await requestJson(config.routes.login, {
                    method: 'POST',
                    body: JSON.stringify({
                        user_id: account.user_id,
                        device_id: account.device_id,
                        device_name: deviceName,
                        token: account.token,
                    }),
                });

                if (data.account) {
                    upsertAccount(data.account);
                }

                showToast(data.message || 'Đăng nhập nhanh thành công.');
                window.location.href = data.redirect_url || '/';
            } catch (error) {
                if (error.data?.remove_account) {
                    removeAccount(account);
                    renderAccounts();
                }

                showToast(error.message, 'error');

                if (error.data?.redirect_url) {
                    setTimeout(() => {
                        window.location.href = error.data.redirect_url;
                    }, 1200);
                }
            } finally {
                setLoading(button, false);
            }
        };

        const deleteSavedAccount = async (account, button) => {
            setLoading(button, true, 'Đang xóa...');

            try {
                const data = await requestJson(config.routes.destroy, {
                    method: 'DELETE',
                    body: JSON.stringify({
                        user_id: account.user_id,
                        device_id: account.device_id,
                        token: account.token,
                    }),
                });

                removeAccount(account);
                renderAccounts();
                showToast(data.message || 'Đã xóa tài khoản đã lưu.');
            } catch (error) {
                showToast(error.message, 'error');
            } finally {
                setLoading(button, false);
            }
        };

        const updateLaunchers = () => {
            const count = accountsForDevice().length;

            document.querySelectorAll('[data-saved-login-count]').forEach((element) => {
                element.textContent = String(count);
            });

            document.querySelectorAll('[data-saved-login-shortcut]').forEach((element) => {
                element.hidden = count === 0;
            });
        };

        document.addEventListener('click', (event) => {
            const menuTrigger = event.target.closest('[data-account-menu-trigger]');

            if (menuTrigger) {
                event.preventDefault();

                const menu = menuTrigger.closest('[data-account-menu]');

                if (!menu) {
                    return;
                }

                const willOpen = !menu.classList.contains('is-open');
                closeAccountMenus(menu);
                menu.classList.toggle('is-open', willOpen);
                menuTrigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                return;
            }

            const opener = event.target.closest('[data-open-account-switcher]');

            if (opener) {
                event.preventDefault();
                closeAccountMenus();
                openSwitchModal();
                return;
            }

            const closer = event.target.closest('[data-lsa-close]');

            if (closer) {
                closeDialog(closer.closest('.lsa-dialog'));
                return;
            }

            if (!event.target.closest('[data-account-menu]')) {
                closeAccountMenus();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') {
                return;
            }

            closeAccountMenus();
            document.querySelectorAll('.lsa-dialog.is-open').forEach(closeDialog);
        });

        document.querySelector('[data-save-account-dismiss]')?.addEventListener('click', () => closeDialog(savePrompt));
        document.querySelector('[data-save-account-confirm]')?.addEventListener('click', (event) => {
            saveCurrentAccount(event.currentTarget);
        });

        updateLaunchers();

        if (config.shouldPromptSave && config.currentUser && !hasCurrentSavedAccount()) {
            openDialog(savePrompt);
        }
    })();
</script>