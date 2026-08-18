document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

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
            btn.querySelector('.like-icon').textContent = res.liked ? '❤️' : '🤍';
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
