document.addEventListener('DOMContentLoaded', function () {
    const iconPaths = {
        'users-round': '<path d="M18 21a6 6 0 0 0-12 0"/><circle cx="12" cy="7" r="4"/><path d="M22 21a5 5 0 0 0-3.5-4.8M19 3.2a4 4 0 0 1 0 7.6"/>',
        zap: '<path d="M13 2 3 14h9l-1 8 10-12h-9l1-8Z"/>',
        'globe-2': '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>',
        apple: '<path fill="currentColor" stroke="none" d="M16.7 13.1c0-2 1.6-3 1.7-3.1-1-.7-2.5-.8-3-.8-1.3-.1-2.5.8-3.2.8-.7 0-1.7-.8-2.8-.8-1.5 0-2.8.9-3.5 2.2-1.5 2.6-.4 6.5 1 8.5.7 1 1.5 2.1 2.5 2.1s1.4-.6 2.7-.6 1.7.6 2.7.6 1.8-1 2.5-2c.8-1.2 1.1-2.4 1.1-2.5-.1 0-2.2-.9-2.2-4.4zM14.6 7.8c.6-.7 1-1.6.9-2.6-.9 0-1.9.6-2.6 1.3-.6.6-1 1.5-.9 2.4 1 0 2-.5 2.6-1.1z"/>',
        house: '<path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V10Z"/><path d="M9 21v-6h6v6"/>',
        compass: '<circle cx="12" cy="12" r="9"/><path d="m16 8-2.5 5.5L8 16l2.5-5.5L16 8Z"/>',
        bell: '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/>',
        mail: '<rect width="18" height="14" x="3" y="5" rx="2"/><path d="m3 7 9 6 9-6"/>',
        'user-round': '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'log-out': '<path d="M10 17l5-5-5-5M15 12H3M21 19V5a2 2 0 0 0-2-2h-5"/>',
        settings: '<path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z"/><path d="m19.4 15 .1.1a2 2 0 0 1-2.8 2.8l-.1-.1a2 2 0 0 0-3.4 1.4v.3a2 2 0 0 1-4 0v-.3a2 2 0 0 0-3.4-1.4l-.1.1A2 2 0 0 1 3 15.1l.1-.1A2 2 0 0 0 1.7 11.6h-.3a2 2 0 0 1 0-4h.3A2 2 0 0 0 3 4.2l-.1-.1A2 2 0 0 1 5.7 1.3l.1.1a2 2 0 0 0 3.4-1.4v-.3a2 2 0 0 1 4 0V0a2 2 0 0 0 3.4 1.4l.1-.1a2 2 0 0 1 2.8 2.8l-.1.1a2 2 0 0 0 1.4 3.4h.3a2 2 0 0 1 0 4h-.3a2 2 0 0 0-1.4 3.4Z"/>',
        'shield-check': '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/>',
        lock: '<rect width="14" height="11" x="5" y="11" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
        sliders: '<path d="M4 21v-7M4 10V3M12 21v-9M12 8V3M20 21v-5M20 12V3M1 14h6M9 8h6M17 16h6"/>',
        brush: '<path d="m9 15 6-6M12 21a9 9 0 0 0 9-9"/><path d="M3 21c2.5 0 4-1.5 4-4 0-2.5 1.5-4 4-4 2.5 0 4-1.5 4-4 0-2.5 1.5-4 4-4"/>',
        'help-circle': '<circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.5 2.5 0 1 1 4.2 1.8c-1 .8-1.7 1.2-1.7 2.7M12 17h.01"/>',
        info: '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>',
        'chevron-right': '<path d="m9 18 6-6-6-6"/>',
        monitor: '<rect width="18" height="12" x="3" y="3" rx="2"/><path d="M8 21h8M12 15v6"/>',
        moon: '<path d="M20.5 14.5A8 8 0 0 1 9.5 3.5 8 8 0 1 0 20.5 14.5Z"/>',
        plus: '<path d="M12 5v14M5 12h14"/>',
        'badge-check': '<path d="M3.8 7.5 5.5 5l3-.2L11 3l2.5 1.8 3 .2 1.7 2.5 2.5 1.7-.2 3 1.2 2.7-1.8 2.5-2.7 1.2-3-.2-2.5 1.2-2.5-1.8-3-.2-1.7-2.5-2.5-1.7.2-3Z"/><path d="m8.5 12 2.2 2.2 4.8-4.8"/>',
        image: '<rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/>',
        'file-image': '<path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v6h6M8 16l2-2 2 2 2-2 2 2"/>',
        'bar-chart-3': '<path d="M3 3v18h18M7 16v2M11 12v6M15 8v10M19 4v14"/>',
        smile: '<circle cx="12" cy="12" r="9"/><path d="M8 14s1.5 2 4 2 4-2 4-2M9 9h.01M15 9h.01"/>',
        'calendar-days': '<rect width="18" height="18" x="3" y="4" rx="2"/><path d="M16 2v4M8 2v4M3 10h18M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01"/>',
        search: '<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>',
        'trending-up': '<path d="m3 17 6-6 4 4 7-8"/><path d="M14 7h6v6"/>',
        'more-horizontal': '<circle cx="5" cy="12" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/>',
        'message-circle': '<path d="M7.9 19a9 9 0 1 0-2.9-2.2L3 21l4.9-2Z"/>',
        'repeat-2': '<path d="m17 2 4 4-4 4M3 11V9a3 3 0 0 1 3-3h15M7 22l-4-4 4-4M21 13v2a3 3 0 0 1-3 3H3"/>',
        heart: '<path d="M20.8 8.6c0 5.5-8.8 10.4-8.8 10.4S3.2 14.1 3.2 8.6A4.6 4.6 0 0 1 12 6.2a4.6 4.6 0 0 1 8.8 2.4Z"/>',
        bookmark: '<path d="M6 4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18l-6-4-6 4V4Z"/>',
        share: '<path d="m12 3 6 6-6 6M18 9H7a4 4 0 0 0-4 4v4"/>',
        'trash-2': '<path d="M3 6h18M8 6V4h8v2M19 6l-1 16H6L5 6M10 11v6M14 11v6"/>',
        info: '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>',
        send: '<path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/>',
    };
    function renderLocalIcons() {
        document.querySelectorAll('[data-lucide]').forEach(function (element) {
            const path = iconPaths[element.dataset.lucide];
            if (!path) return;
            const className = element.getAttribute('class');
            const classAttribute = className ? ' class="' + className + '"' : '';
            element.outerHTML = '<svg' + classAttribute + ' xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + path + '</svg>';
        });
    }
    const authIconMap = [
        ['.people-icon', 'users-round'],
        ['.spark-icon', 'zap'],
        ['.globe-icon', 'globe-2'],
        ['.apple-mark', 'apple'],
    ];
    authIconMap.forEach(function ([selector, icon]) {
        const element = document.querySelector(selector);
        if (element) element.innerHTML = '<i data-lucide="' + icon + '"></i>';
    });
    const navIconMap = { 'home.php': 'house', 'explore.php': 'compass', 'notifications.php': 'bell', 'messages.php': 'mail', 'profile.php': 'user-round', 'userlist.php': 'users-round', 'settings.php': 'settings', 'logout.php': 'log-out' };
    document.querySelectorAll('.nav-link').forEach(function (link) {
        const icon = navIconMap[link.getAttribute('href')?.split('?')[0]];
        const iconHolder = link.querySelector('.nav-icon');
        if (icon && iconHolder) iconHolder.innerHTML = '<i data-lucide="' + icon + '"></i>';
    });
    renderLocalIcons();

    const themeButtons = document.querySelectorAll('.nav-theme');
    const appearanceDefaults = { theme: 'dark', accent: 'purple', density: 'standard', textSize: 'medium', animations: true, reduceMotion: false, highContrast: false, reduceTransparency: false, focusIndicators: true };
    function applyAppearance(settings) {
        const systemLight = settings.theme === 'system' && window.matchMedia('(prefers-color-scheme: light)').matches;
        document.body.classList.toggle('light-theme', settings.theme === 'light' || systemLight);
        document.body.dataset.accent = settings.accent;
        document.body.dataset.density = settings.density;
        document.body.dataset.textSize = settings.textSize;
        document.body.classList.toggle('reduce-motion', settings.reduceMotion || !settings.animations);
        document.body.classList.toggle('high-contrast', settings.highContrast);
        document.body.classList.toggle('reduce-transparency', settings.reduceTransparency);
        document.body.classList.toggle('focus-indicators', settings.focusIndicators);
        themeButtons.forEach((button) => { button.setAttribute('aria-pressed', String(settings.theme === 'light')); });
        document.querySelectorAll('[data-appearance-control]').forEach((control) => {
            const key = control.dataset.appearanceControl;
            if (control.type === 'checkbox') control.checked = Boolean(settings[key]);
            else if (control.type === 'radio') control.checked = settings[key] === control.value;
            else control.value = settings[key];
        });
    }
    function savedAppearance() { try { return { ...appearanceDefaults, ...JSON.parse(localStorage.getItem('postard-appearance') || '{}') }; } catch (error) { return { ...appearanceDefaults }; } }
    let appearance = savedAppearance();
    applyAppearance(appearance);
    themeButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            appearance.theme = appearance.theme === 'light' ? 'dark' : 'light';
            localStorage.setItem('postard-appearance', JSON.stringify(appearance));
            applyAppearance(appearance);
        });
    });
    document.querySelectorAll('[data-appearance-control]').forEach(function (control) {
        control.addEventListener('change', function () {
            const key = control.dataset.appearanceControl;
            appearance[key] = control.type === 'checkbox' ? control.checked : control.value;
            localStorage.setItem('postard-appearance', JSON.stringify(appearance));
            applyAppearance(appearance);
        });
    });
    document.querySelectorAll('[data-language-choice]').forEach(function (button) {
        button.addEventListener('click', function () {
            const description = button.querySelector('.settings-copy span');
            if (description) description.textContent = 'English (default language)';
        });
    });
    document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
        button.addEventListener('click', function () {
            const form = button.parentElement.querySelector('.account-password-form');
            if (form) form.hidden = !form.hidden;
        });
    });
    document.querySelectorAll('[data-delete-account]').forEach(function (button) {
        const modal = document.querySelector('.account-modal');
        const cancel = modal?.querySelector('[data-delete-cancel]');
        if (!modal) return;
        button.addEventListener('click', function () { modal.hidden = false; cancel?.focus(); });
        cancel?.addEventListener('click', function () { modal.hidden = true; button.focus(); });
        modal.addEventListener('click', function (event) { if (event.target === modal) modal.hidden = true; });
        document.addEventListener('keydown', function (event) { if (event.key === 'Escape') modal.hidden = true; });
    });

    document.querySelectorAll('.nav-user').forEach(function (trigger) {
        const menu = document.getElementById(trigger.getAttribute('aria-controls'));
        if (!menu) return;
        function closeMenu() {
            menu.hidden = true;
            trigger.setAttribute('aria-expanded', 'false');
        }
        trigger.addEventListener('click', function () {
            const open = menu.hidden;
            menu.hidden = !open;
            trigger.setAttribute('aria-expanded', String(open));
        });
        document.addEventListener('click', function (event) {
            if (!trigger.contains(event.target) && !menu.contains(event.target)) closeMenu();
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') closeMenu();
        });
        menu.querySelector('.user-menu-settings')?.addEventListener('click', closeMenu);
    });

    document.querySelectorAll('.enable-notifications').forEach(function (button) {
        if (!('Notification' in window)) {
            button.disabled = true;
            button.textContent = 'Not supported';
            return;
        }
        if (Notification.permission === 'granted') button.textContent = 'Notifications enabled';
        button.addEventListener('click', function () {
            Notification.requestPermission().then(function (permission) {
                button.textContent = permission === 'granted' ? 'Notifications enabled' : 'Enable Notifications';
            });
        });
    });

    document.querySelectorAll('.message-pane').forEach(function (pane) {
        const searchToggle = pane.querySelector('.chat-search-toggle');
        const searchBar = pane.querySelector('.chat-search-bar');
        const searchInput = searchBar?.querySelector('input');
        const infoToggle = pane.querySelector('.chat-info-toggle');
        const infoPanel = pane.querySelector('.chat-info-panel');
        if (searchToggle && searchBar && searchInput) {
            searchToggle.addEventListener('click', function () {
                const isHidden = searchBar.hidden;
                searchBar.hidden = !isHidden;
                searchToggle.setAttribute('aria-expanded', String(isHidden));
                if (isHidden) searchInput.focus();
                else {
                    searchInput.value = '';
                    pane.querySelectorAll('.message-entry').forEach((entry) => { entry.hidden = false; });
                }
            });
            searchInput.addEventListener('input', function () {
                const query = searchInput.value.trim().toLowerCase();
                pane.querySelectorAll('.message-entry').forEach(function (entry) {
                    entry.hidden = query !== '' && !entry.textContent.toLowerCase().includes(query);
                });
            });
        }
        if (infoToggle && infoPanel) {
            infoToggle.addEventListener('click', function () {
                infoPanel.hidden = !infoPanel.hidden;
                infoToggle.setAttribute('aria-expanded', String(!infoPanel.hidden));
            });
        }
    });

    document.querySelectorAll('.message-history').forEach(function (history) {
        history.scrollTop = history.scrollHeight;
    });

    // The legacy image picker is visually replaced by the dashboard composer toolbar.
    document.querySelectorAll('.composer-footer > .file-label input[type="file"]').forEach(function (input) {
        input.disabled = true;
    });

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    document.querySelectorAll('.password-toggle').forEach(function (button) {
        button.addEventListener('click', function () {
            const input = button.parentElement.querySelector('input');
            const visible = input.type === 'text';
            input.type = visible ? 'password' : 'text';
            button.setAttribute('aria-label', visible ? 'Show password' : 'Hide password');
        });
    });

    function post(url, data) {
        const body = new URLSearchParams({ ...data, csrf_token: csrfToken });
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body,
        }).then((r) => r.json());
    }

    // Like toggle
    document.body.addEventListener('click', function (e) {
        const btn = e.target.closest('.like-btn');
        if (!btn) return;
        const id = btn.dataset.id;
        post('like.php', { id }).then((res) => {
            if (!res.ok) return;
            btn.classList.toggle('active', res.liked);
            btn.querySelector('.like-count').textContent = res.count;
        });
    });

    // Repost toggle
    document.body.addEventListener('click', function (e) {
        const btn = e.target.closest('.repost-btn');
        if (!btn) return;
        const id = btn.dataset.id;
        post('retweet.php', { id }).then((res) => {
            if (!res.ok) return;
            btn.classList.toggle('active', res.reposted);
            btn.querySelector('.repost-count').textContent = res.count;
        });
    });

    // Bookmark toggle
    document.body.addEventListener('click', function (e) {
        const btn = e.target.closest('.bookmark-btn');
        if (!btn) return;
        post('bookmark.php', { id: btn.dataset.id }).then((res) => {
            if (res.ok) btn.classList.toggle('active', res.bookmarked);
        });
    });

    // Share a post using the browser clipboard when available.
    document.body.addEventListener('click', function (e) {
        const btn = e.target.closest('.share-btn');
        if (!btn) return;
        const url = new URL('tweet.php?id=' + encodeURIComponent(btn.dataset.id), window.location.href).href;
        const copied = navigator.clipboard ? navigator.clipboard.writeText(url) : Promise.reject();
        copied.then(() => { btn.classList.add('shared'); setTimeout(() => btn.classList.remove('shared'), 900); }).catch(() => {
            window.prompt('Copy this post link:', url);
        });
    });

    // Delete tweet
    document.body.addEventListener('click', function (e) {
        const btn = e.target.closest('.delete-btn');
        if (!btn) return;
        if (!confirm('Delete this post?')) return;
        const id = btn.dataset.id;
        post('delete_tweet.php', { id }).then((res) => {
            if (res.ok) {
                btn.closest('.tweet').remove();
            }
        });
    });

    // Reply button -> jump to tweet detail page compose box
    document.body.addEventListener('click', function (e) {
        const btn = e.target.closest('.reply-btn');
        if (!btn) return;
        window.location.href = 'tweet.php?id=' + encodeURIComponent(btn.dataset.id) + '#reply-box';
    });

    // Composer character counter + image preview
    document.querySelectorAll('.composer-form').forEach(function (form) {
        const textarea = form.querySelector('textarea');
        const counter = form.querySelector('.char-count');
        const fileInput = form.querySelector('input[type="file"]');
        const preview = form.querySelector('.image-preview');
        const submitBtn = form.querySelector('button[type="submit"]');
        const maxLen = parseInt(form.dataset.maxlen || '280', 10);

        function updateCount() {
            const len = textarea.value.length;
            counter.textContent = len + ' / ' + maxLen;
            counter.classList.toggle('warn', len > maxLen * 0.8 && len <= maxLen);
            counter.classList.toggle('over', len > maxLen);
            submitBtn.disabled = len === 0 || len > maxLen;
        }
        if (textarea) {
            textarea.addEventListener('input', updateCount);
            updateCount();
        }
        if (fileInput && preview) {
            fileInput.addEventListener('change', function () {
                const file = fileInput.files[0];
                if (!file) { preview.style.display = 'none'; return; }
                const reader = new FileReader();
                reader.onload = (ev) => {
                    preview.src = ev.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            });
        }
    });

    // Poll unread notification count every 30s
    const badgeHolder = document.querySelector('.nav-link[href="notifications.php"]');
    if (badgeHolder) {
        setInterval(function () {
            fetch('notifications_count.php', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then((r) => r.json())
                .then((res) => {
                    let badge = badgeHolder.querySelector('.badge');
                    if (res.count > 0) {
                        if (!badge) {
                            badge = document.createElement('span');
                            badge.className = 'badge';
                            badgeHolder.appendChild(badge);
                        }
                        badge.textContent = res.count;
                    } else if (badge) {
                        badge.remove();
                    }
                })
                .catch(() => {});
        }, 30000);
    }
});
