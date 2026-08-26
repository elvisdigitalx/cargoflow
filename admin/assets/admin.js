/* =====================================================================
   CargoFlow — Admin dashboard JavaScript (vanilla)
   ===================================================================== */
(function () {
    'use strict';

    var CSRF = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

    /* ---------------- Page preloader ---------------- */
    function hidePreloader() {
        var pre = document.getElementById('cfPreloader');
        if (!pre || pre.classList.contains('done')) return;
        pre.classList.add('done');
        setTimeout(function () { if (pre.parentNode) pre.parentNode.removeChild(pre); }, 600);
    }
    if (document.readyState === 'complete') {
        hidePreloader();
    } else {
        window.addEventListener('load', hidePreloader);
        setTimeout(hidePreloader, 4000);
    }
    window.addEventListener('pageshow', function (e) { if (e.persisted) hidePreloader(); });

    /* ---------------- Theme ---------------- */
    function applyTheme(theme) {
        document.documentElement.setAttribute('data-bs-theme', theme);
        var icons = document.querySelectorAll('[data-theme-icon]');
        icons.forEach(function (i) { i.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill'; });
    }
    function initTheme() {
        var t = localStorage.getItem('cf_theme') || document.documentElement.getAttribute('data-bs-theme') || 'light';
        applyTheme(t);
    }
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-theme-toggle]');
        if (btn) {
            var cur = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
            var next = cur === 'dark' ? 'light' : 'dark';
            localStorage.setItem('cf_theme', next);
            document.cookie = 'cf_theme=' + next + '; path=/; max-age=31536000; SameSite=Lax';
            applyTheme(next);
        }
    });

    /* ---------------- Sidebar (mobile) ---------------- */
    document.addEventListener('click', function (e) {
        var toggler = e.target.closest('[data-sidebar-toggle]');
        if (toggler) {
            var sidebar = document.getElementById('adminSidebar');
            sidebar.classList.toggle('open');
            var backdrop = document.querySelector('.sidebar-backdrop');
            if (sidebar.classList.contains('open')) {
                if (!backdrop) {
                    backdrop = document.createElement('div');
                    backdrop.className = 'sidebar-backdrop d-lg-none';
                    document.body.appendChild(backdrop);
                }
                backdrop.addEventListener('click', closeSidebar);
            } else if (backdrop) { backdrop.remove(); }
        }
    });
    function closeSidebar() {
        document.getElementById('adminSidebar').classList.remove('open');
        var backdrop = document.querySelector('.sidebar-backdrop');
        if (backdrop) backdrop.remove();
    }

    /* ---------------- Toast ---------------- */
    function toast(message, type) {
        var container = document.querySelector('.cf-toast');
        if (!container) {
            container = document.createElement('div');
            container.className = 'cf-toast';
            document.body.appendChild(container);
        }
        var el = document.createElement('div');
        el.className = 'toast align-items-center text-bg-' + (type || 'success') + ' border-0 show';
        el.innerHTML = '<div class="d-flex"><div class="toast-body">' + message + '</div>' +
            '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>';
        container.appendChild(el);
        setTimeout(function () { el.classList.remove('show'); setTimeout(function () { el.remove(); }, 400); }, 4000);
    }

    /* ---------------- API helper ---------------- */
    function api(url, data, method) {
        method = method || (data ? 'POST' : 'GET');
        var opts = {
            method: method,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': CSRF }
        };
        if (data) {
            if (data instanceof FormData) {
                opts.body = data;
            } else {
                var body = new FormData();
                Object.keys(data).forEach(function (k) { body.append(k, data[k] === null || data[k] === undefined ? '' : data[k]); });
                opts.body = body;
            }
        }
        return fetch(url, opts).then(function (res) {
            // Session expired → send the user back to login instead of
            // leaving every widget stuck on "Loading…".
            if (res.status === 401) {
                window.location.href = (window.CF_LOGIN_URL || '../login.php');
                return new Promise(function () {}); // halt the chain
            }
            return res.json().catch(function () {
                return { success: false, message: 'Unexpected server response.' };
            });
        }).then(function (json) {
            // Normalize: guarantee json.data exists so page scripts doing
            // json.data.map(...) never crash and hang on the loading row.
            if (json && json.success === false) {
                toast(json.message || 'Request failed.', 'danger');
            }
            if (json && json.data === undefined) json.data = [];
            return json;
        }).catch(function () {
            toast('Network error. Please check your connection.', 'danger');
            return { success: false, data: [], message: 'Network error' };
        });
    }

    /* ---------------- Confirm delete helper ---------------- */
    window.confirmAction = function (message, onConfirm) {
        if (confirm(message || 'Are you sure?')) { onConfirm(); }
    };

    /* ---------------- Modal form submission (data-modal-form) ---------------- */
    window.CFModalSubmit = function (form, onSuccess) {
        var btn = form.querySelector('[type="submit"]');
        var orig = btn ? btn.innerHTML : '';
        if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…'; }
        // Send the raw FormData so file inputs (e.g. package photo) are preserved.
        var data = new FormData(form);
        api(form.getAttribute('action'), data).then(function (json) {
            if (json.success === false) {
                toast(json.message || 'Error', 'danger');
            } else if (json.success) {
                toast(json.message || 'Saved', 'success');
                if (onSuccess) onSuccess(json);
            }
        }).catch(function () { toast('Network error', 'danger'); })
          .finally(function () { if (btn) { btn.disabled = false; btn.innerHTML = orig; } });
    };

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (form.hasAttribute('data-modal-form')) {
            e.preventDefault();
            window.CFModalSubmit(form, typeof window[form.getAttribute('data-on-success')] === 'function' ? window[form.getAttribute('data-on-success')] : null);
        }
    });

    window.CF = { toast: toast, api: api, csrf: CSRF };

    /* ---------------- Topbar notifications (badge + dropdown) ---------------- */
    function initNotifications() {
        var badge = document.getElementById('notifBadge');
        var dropdown = document.getElementById('notifDropdown');
        if (!badge || !dropdown) return;

        var apiUrl = (document.querySelector('meta[name="cf-api-notifications"]') || {}).content;
        if (!apiUrl) {
            // Derive from current location: /admin/... → ../api/notifications.php
            apiUrl = window.location.pathname.replace(/\/admin\/[^\/]*$/, '/api/notifications.php');
        }

        function esc(v) {
            return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) {
                return { '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[c];
            });
        }

        function render(json) {
            var unread = json.unread || 0;
            badge.textContent = unread > 99 ? '99+' : String(unread);
            badge.classList.toggle('d-none', unread === 0);

            var rows = (json.data || []).slice(0, 8);
            if (!rows.length) {
                dropdown.innerHTML = '<div class="text-muted-2 small px-2 py-3 text-center">No notifications</div>';
                return;
            }
            var icons = { info:'bi-info-circle text-primary', success:'bi-check-circle text-success', warning:'bi-exclamation-triangle text-warning', error:'bi-x-circle text-danger' };
            dropdown.innerHTML = rows.map(function (n) {
                return '<div class="d-flex align-items-start gap-2 px-2 py-2 border-bottom' + (n.is_read == 0 ? ' fw-semibold' : '') + '">' +
                    '<i class="bi ' + (icons[n.type] || 'bi-info-circle') + '"></i>' +
                    '<div class="flex-grow-1 small">' +
                        '<div>' + esc(n.title) + '</div>' +
                        '<div class="text-muted-2 fw-normal">' + esc(n.message || '') + '</div>' +
                    '</div></div>';
            }).join('') +
            '<div class="px-2 pt-2 text-center"><a class="small" href="notifications.php">View all</a></div>';
        }

        function refresh() {
            api(apiUrl + '?limit=8').then(function (json) {
                if (json && json.success) render(json);
                else dropdown.innerHTML = '<div class="text-muted-2 small px-2 py-3 text-center">Could not load notifications</div>';
            });
        }

        refresh();
        setInterval(refresh, 60000);
    }

    function init() {
        initTheme();
        initNotifications();
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
