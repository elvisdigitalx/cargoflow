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
            return res.json().catch(function () { return {}; });
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

    document.addEventListener('DOMContentLoaded', initTheme);
})();
