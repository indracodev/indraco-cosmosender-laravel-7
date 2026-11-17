/*!
 * CosmoSender Global Native JS
 * Berdasarkan dokumentasi resmi Bootstrap 5.3 Color Modes & Vanilla ES6+
 */
(() => {
    'use strict';

    const THEME_STORAGE_KEY = 'cs_theme';
    const IDLE_TIMEOUT_MS = 15 * 60 * 1000; // 15 Menit

    const getStoredTheme = () => localStorage.getItem(THEME_STORAGE_KEY);
    const setStoredTheme = (theme) => localStorage.setItem(THEME_STORAGE_KEY, theme);

    const getPreferredTheme = () => {
        const storedTheme = getStoredTheme();
        if (storedTheme) {
            return storedTheme;
        }
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    };

    const resolveEffectiveTheme = (theme) => {
        if (theme === 'auto') {
            return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        return theme === 'dark' ? 'dark' : 'light';
    };

    const syncThemeImages = (effectiveTheme) => {
        document.querySelectorAll('img[data-img-src-dark], img[data-img-src-light]').forEach((img) => {
            const targetSrc = effectiveTheme === 'dark'
                ? (img.getAttribute('data-img-src-dark') || img.getAttribute('data-img-src-light'))
                : (img.getAttribute('data-img-src-light') || img.getAttribute('data-img-src-dark'));

            if (targetSrc && img.getAttribute('src') !== targetSrc) {
                img.setAttribute('src', targetSrc);
            }
        });
    };

    const showActiveTheme = (theme) => {
        const effectiveTheme = resolveEffectiveTheme(theme);

        document.querySelectorAll('[data-toggle-theme]').forEach((btn) => {
            const icon = btn.querySelector('i');
            if (icon) {
                icon.classList.remove('bi-moon-stars-fill', 'bi-sun-fill');
                icon.classList.add(effectiveTheme === 'dark' ? 'bi-sun-fill' : 'bi-moon-stars-fill');
            }
            const label = effectiveTheme === 'dark' ? 'Beralih ke Mode Terang' : 'Beralih ke Mode Gelap';
            btn.setAttribute('title', label);
            btn.setAttribute('aria-label', label);
        });

        document.querySelectorAll('[data-bs-theme-value]').forEach((element) => {
            const isActive = element.getAttribute('data-bs-theme-value') === theme;
            element.classList.toggle('active', isActive);
            element.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });

        syncThemeImages(effectiveTheme);
    };

    const setTheme = (theme) => {
        const effectiveTheme = resolveEffectiveTheme(theme);
        document.documentElement.setAttribute('data-bs-theme', effectiveTheme);
    };

    setTheme(getPreferredTheme());

    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
        const storedTheme = getStoredTheme();
        if (storedTheme !== 'light' && storedTheme !== 'dark') {
            const preferred = getPreferredTheme();
            setTheme(preferred);
            showActiveTheme(preferred);
        }
    });

    // Penanganan Auto-Logout Idle 15 Menit (Aman terhadap Aktivitas Blasting)
    let idleTimer = null;
    window.isBlastActive = false;

    const triggerIdleLogout = () => {
        if (window.isBlastActive) {
            resetIdleTimer();
            return;
        }
        const logoutForm = document.querySelector('form[action*="logout"]');
        if (logoutForm) {
            logoutForm.submit();
        }
    };

    const resetIdleTimer = () => {
        if (!idleTimer && !document.querySelector('form[action*="logout"]')) {
            return;
        }
        clearTimeout(idleTimer);
        idleTimer = setTimeout(triggerIdleLogout, IDLE_TIMEOUT_MS);
    };

    window.resetIdleTimer = resetIdleTimer;

    const syncSidebarMiniButtons = () => {
        const isMini = document.documentElement.getAttribute('data-sidebar-mini') === 'true';
        document.querySelectorAll('[data-toggle-sidebar-mini]').forEach((btn) => {
            const icon = btn.querySelector('i');
            if (icon) {
                icon.classList.remove('bi-layout-sidebar-inset', 'bi-layout-sidebar');
                icon.classList.add(isMini ? 'bi-layout-sidebar' : 'bi-layout-sidebar-inset');
            }
            const label = isMini ? 'Perlebar Sidebar' : 'Perkecil Sidebar (Ikon Saja)';
            btn.setAttribute('title', label);
            btn.setAttribute('aria-label', label);
        });
    };

    document.addEventListener('DOMContentLoaded', () => {
        showActiveTheme(getPreferredTheme());
        syncSidebarMiniButtons();

        if (document.querySelector('form[action*="logout"]')) {
            resetIdleTimer();
            ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart', 'click'].forEach((evtName) => {
                document.addEventListener(evtName, resetIdleTimer, { passive: true });
            });
        }
    });

    // Event Delegation Global: Theme Switcher & Password Toggle
    document.addEventListener('click', (event) => {
        const themeValueBtn = event.target.closest('[data-bs-theme-value]');
        if (themeValueBtn) {
            const theme = themeValueBtn.getAttribute('data-bs-theme-value');
            setStoredTheme(theme);
            setTheme(theme);
            showActiveTheme(theme);
            return;
        }

        const themeToggleBtn = event.target.closest('[data-toggle-theme]');
        if (themeToggleBtn) {
            const current = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
            const next = current === 'dark' ? 'light' : 'dark';
            setStoredTheme(next);
            setTheme(next);
            showActiveTheme(next);
            return;
        }

        const sidebarMiniBtn = event.target.closest('[data-toggle-sidebar-mini]');
        if (sidebarMiniBtn) {
            const isCurrentlyMini = document.documentElement.getAttribute('data-sidebar-mini') === 'true';
            const nextMini = !isCurrentlyMini;
            if (nextMini) {
                document.documentElement.setAttribute('data-sidebar-mini', 'true');
            } else {
                document.documentElement.removeAttribute('data-sidebar-mini');
            }
            localStorage.setItem('cs_sidebar_mini', nextMini ? 'true' : 'false');
            syncSidebarMiniButtons();
            setTimeout(() => {
                window.dispatchEvent(new Event('resize'));
            }, 220);
            return;
        }

        const pwToggleBtn = event.target.closest('[data-toggle-password]');
        if (pwToggleBtn) {
            const targetSelector = pwToggleBtn.getAttribute('data-target');
            const group = pwToggleBtn.closest('.input-group');
            const input = targetSelector
                ? document.querySelector(targetSelector)
                : (group ? group.querySelector('input') : null);

            if (!input) return;

            const isPassword = input.getAttribute('type') === 'password';
            input.setAttribute('type', isPassword ? 'text' : 'password');

            const icon = pwToggleBtn.querySelector('i');
            if (icon) {
                icon.classList.toggle('bi-eye', !isPassword);
                icon.classList.toggle('bi-eye-slash', isPassword);
            }
            pwToggleBtn.setAttribute('aria-label', isPassword ? 'Sembunyikan password' : 'Tampilkan password');
        }
    });

    // Reset seluruh field password ke tipe 'password' ketika Modal Bootstrap ditutup
    document.addEventListener('hidden.bs.modal', (event) => {
        const modal = event.target;
        if (!modal) return;

        modal.querySelectorAll('[data-toggle-password]').forEach((btn) => {
            const targetSelector = btn.getAttribute('data-target');
            const group = btn.closest('.input-group');
            const input = targetSelector
                ? document.querySelector(targetSelector)
                : (group ? group.querySelector('input') : null);

            if (input) {
                input.setAttribute('type', 'password');
            }
            const icon = btn.querySelector('i');
            if (icon) {
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
            btn.setAttribute('aria-label', 'Tampilkan password');
        });
    });

    // Global Helper: Bootstrap Modal
    window.showModal = (modalIdOrEl) => {
        const el = typeof modalIdOrEl === 'string'
            ? document.getElementById(modalIdOrEl.replace(/^#/, ''))
            : modalIdOrEl;
        if (el && window.bootstrap) {
            window.bootstrap.Modal.getOrCreateInstance(el).show();
        }
    };

    window.hideModal = (modalIdOrEl) => {
        const el = typeof modalIdOrEl === 'string'
            ? document.getElementById(modalIdOrEl.replace(/^#/, ''))
            : modalIdOrEl;
        if (el && window.bootstrap) {
            window.bootstrap.Modal.getOrCreateInstance(el).hide();
        }
    };

    // Global Helper: Ekstrak Pesan Error JSON Laravel
    window.extractErrorMessage = (errorData) => {
        if (!errorData) {
            return 'Terjadi kesalahan pada permintaan server.';
        }
        if (typeof errorData === 'string') {
            return errorData;
        }
        if (errorData.errors) {
            const firstKey = Object.keys(errorData.errors)[0];
            if (firstKey && errorData.errors[firstKey][0]) {
                return errorData.errors[firstKey][0];
            }
        }
        if (errorData.message) {
            return errorData.message;
        }
        return 'Terjadi kesalahan pada permintaan server.';
    };

    // Global Helper: Native Fetch AJAX ke Backend Laravel
    window.csRequest = async (url, options = {}) => {
        // Setiap aktivitas AJAX (termasuk proses Blasting) me-reset idle timer
        resetIdleTimer();

        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

        const method = (options.method || 'GET').toUpperCase();
        const headers = Object.assign({
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }, options.headers || {});

        if (csrfToken && method !== 'GET') {
            headers['X-CSRF-TOKEN'] = csrfToken;
        }

        let body = options.body || null;
        let targetUrl = url;

        if (options.form instanceof HTMLFormElement) {
            const formData = new FormData(options.form);
            if (method === 'PUT' || method === 'PATCH' || method === 'DELETE') {
                formData.append('_method', method);
            }
            body = formData;
        } else if (options.data) {
            if (options.data instanceof FormData) {
                body = options.data;
            } else if (method === 'GET') {
                const params = new URLSearchParams(options.data).toString();
                if (params) {
                    targetUrl += (targetUrl.includes('?') ? '&' : '?') + params;
                }
            } else {
                headers['Content-Type'] = 'application/json';
                body = JSON.stringify(options.data);
            }
        }

        const fetchMethod = (body instanceof FormData && (method === 'PUT' || method === 'PATCH' || method === 'DELETE'))
            ? 'POST'
            : method;

        const response = await fetch(targetUrl, {
            method: fetchMethod,
            headers,
            body
        });

        resetIdleTimer();

        let payload = null;
        const contentType = response.headers.get('content-type') || '';
        if (contentType.includes('application/json')) {
            payload = await response.json();
        } else {
            payload = { message: await response.text() };
        }

        if (response.status === 401) {
            window.location.reload();
        }

        if (!response.ok) {
            const err = new Error(window.extractErrorMessage(payload));
            err.status = response.status;
            err.payload = payload;
            throw err;
        }

        return payload;
    };
})();
