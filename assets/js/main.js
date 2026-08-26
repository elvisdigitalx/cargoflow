/* =====================================================================
   CargoFlow — Public site JavaScript (vanilla, no dependencies)
   ===================================================================== */
(function () {
    'use strict';

    /* ---------------- Theme (dark / light mode) ---------------- */
    function applyTheme(theme) {
        document.documentElement.setAttribute('data-bs-theme', theme);
        document.documentElement.setAttribute('data-theme', theme);
        var icons = document.querySelectorAll('[data-theme-icon]');
        icons.forEach(function (icon) {
            icon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
        });
    }

    function initTheme() {
        var stored = localStorage.getItem('cf_theme');
        if (!stored) {
            stored = document.documentElement.getAttribute('data-theme') || 'light';
        }
        applyTheme(stored);
    }

    function toggleTheme() {
        var current = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
        var next = current === 'dark' ? 'light' : 'dark';
        localStorage.setItem('cf_theme', next);
        document.cookie = 'cf_theme=' + next + '; path=/; max-age=31536000; SameSite=Lax';
        applyTheme(next);
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-theme-toggle]');
        if (btn) {
            e.preventDefault();
            toggleTheme();
        }
    });

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
        // Safety net: never keep users staring at the loader.
        setTimeout(hidePreloader, 4000);
    }
    // Restore instantly when navigating back from bfcache.
    window.addEventListener('pageshow', function (e) { if (e.persisted) hidePreloader(); });

    /* ---------------- Minimal language switcher (left side) ---------------- */
    (function () {
        var wrap = document.getElementById('cfLangSwitcher');
        if (!wrap) return;
        var btn = document.getElementById('cfLangBtn');
        var menu = document.getElementById('cfLangMenu');
        var current = document.getElementById('cfLangCurrent');

        var LANGS = [
            { code: 'en',    short: 'EN', label: 'English' },
            { code: 'fr',    short: 'FR', label: 'Français' },
            { code: 'es',    short: 'ES', label: 'Español' },
            { code: 'pt',    short: 'PT', label: 'Português' },
            { code: 'de',    short: 'DE', label: 'Deutsch' },
            { code: 'it',    short: 'IT', label: 'Italiano' },
            { code: 'zh-CN', short: 'ZH', label: '中文' },
            { code: 'ar',    short: 'AR', label: 'العربية' },
            { code: 'sw',    short: 'SW', label: 'Kiswahili' },
            { code: 'yo',    short: 'YO', label: 'Yorùbá' },
            { code: 'ha',    short: 'HA', label: 'Hausa' },
            { code: 'ig',    short: 'IG', label: 'Igbo' }
        ];

        function getActiveLang() {
            var m = document.cookie.match(/(?:^|;\s*)googtrans=([^;]+)/);
            if (m) {
                var parts = decodeURIComponent(m[1]).split('/');
                var code = parts[parts.length - 1];
                if (code && code !== 'en' && code !== 'auto') return code;
            }
            return 'en';
        }

        function setCombo(code) {
            var combo = document.querySelector('select.goog-te-combo');
            if (!combo) return false;
            combo.value = code === 'en' ? '' : code;
            combo.dispatchEvent(new Event('change'));
            return true;
        }

        function clearGoogTransCookie() {
            var host = window.location.hostname;
            var domains = ['', host];
            var p = host.split('.');
            while (p.length > 2) { p.shift(); domains.push('.' + p.join('.')); }
            if (p.length === 2) domains.push('.' + p.join('.'));
            domains.forEach(function (d) {
                document.cookie = 'googtrans=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT' + (d ? '; domain=' + d : '');
            });
        }

        function selectLang(code) {
            wrap.classList.remove('open');
            btn.setAttribute('aria-expanded', 'false');
            if (code === 'en') {
                // Restoring the original language reliably needs a clean reload.
                clearGoogTransCookie();
                document.cookie = 'googtrans=/en/en; path=/';
                window.location.reload();
                return;
            }
            if (!setCombo(code)) {
                // Widget not ready yet — set cookie and reload as a fallback.
                document.cookie = 'googtrans=/en/' + code + '; path=/';
                window.location.reload();
                return;
            }
            updateUI(code);
        }

        function updateUI(code) {
            var lang = LANGS.filter(function (l) { return l.code === code; })[0] || LANGS[0];
            current.textContent = lang.short;
            menu.querySelectorAll('button').forEach(function (b) {
                b.classList.toggle('active', b.getAttribute('data-lang') === lang.code);
            });
        }

        // Build menu
        LANGS.forEach(function (l) {
            var b = document.createElement('button');
            b.type = 'button';
            b.setAttribute('role', 'menuitem');
            b.setAttribute('data-lang', l.code);
            b.textContent = l.label;
            b.addEventListener('click', function () { selectLang(l.code); });
            menu.appendChild(b);
        });
        updateUI(getActiveLang());

        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            var open = wrap.classList.toggle('open');
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
        document.addEventListener('click', function (e) {
            if (!wrap.contains(e.target)) {
                wrap.classList.remove('open');
                btn.setAttribute('aria-expanded', 'false');
            }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                wrap.classList.remove('open');
                btn.setAttribute('aria-expanded', 'false');
            }
        });
    })();

    /* ---------------- Navbar scroll state ---------------- */
    var navbar = document.querySelector('.navbar-cf');
    function onScroll() {
        if (navbar) {
            navbar.classList.toggle('scrolled', window.scrollY > 20);
        }
    }

    /* ---------------- Reveal on scroll ---------------- */
    var revealEls = document.querySelectorAll('.reveal');
    if ('IntersectionObserver' in window && revealEls.length) {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });
        revealEls.forEach(function (el) { observer.observe(el); });
    } else {
        revealEls.forEach(function (el) { el.classList.add('in'); });
    }

    /* ---------------- Animated counters ---------------- */
    var counters = document.querySelectorAll('[data-count]');
    if ('IntersectionObserver' in window && counters.length) {
        var cObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                var el = entry.target;
                var target = parseFloat(el.getAttribute('data-count'));
                var decimals = (el.getAttribute('data-decimals') || '0') | 0;
                var duration = 1600;
                var start = null;
                function step(ts) {
                    if (!start) start = ts;
                    var progress = Math.min((ts - start) / duration, 1);
                    var eased = 1 - Math.pow(1 - progress, 3);
                    var value = target * eased;
                    el.textContent = decimals > 0 ? value.toFixed(decimals) : Math.round(value).toLocaleString();
                    if (progress < 1) requestAnimationFrame(step);
                }
                requestAnimationFrame(step);
                cObserver.unobserve(el);
            });
        }, { threshold: 0.4 });
        counters.forEach(function (el) { cObserver.observe(el); });
    }

    /* ---------------- Toast helper ---------------- */
    window.CFToast = function (message, type) {
        type = type || 'success';
        var container = document.querySelector('.cf-toast');
        if (!container) {
            container = document.createElement('div');
            container.className = 'cf-toast';
            document.body.appendChild(container);
        }
        var el = document.createElement('div');
        el.className = 'toast align-items-center text-bg-' + type + ' border-0 show';
        el.setAttribute('role', 'alert');
        el.innerHTML = '<div class="d-flex"><div class="toast-body">' + message + '</div>' +
            '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>';
        container.appendChild(el);
        setTimeout(function () {
            el.classList.remove('show');
            setTimeout(function () { el.remove(); }, 400);
        }, 4200);
    };

    /* ---------------- Global AJAX submit helper (data-ajax forms) ---------------- */
    window.CFSubmit = function (form, onSuccess) {
        var btn = form.querySelector('[type="submit"]');
        var original = btn ? btn.innerHTML : '';
        if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Submitting…'; }

        var data = new FormData(form);
        if (!data.has('csrf_token')) {
            var csrf = document.querySelector('meta[name="csrf-token"]');
            if (csrf) data.append('csrf_token', csrf.getAttribute('content'));
        }

        fetch(form.getAttribute('action') || window.location.href, {
            method: 'POST',
            body: data,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (res) { return res.json().catch(function () { return {}; }); })
            .then(function (json) {
                if (json.success === false) {
                    CFToast(json.message || 'Something went wrong.', 'danger');
                } else if (onSuccess) {
                    onSuccess(json);
                }
            })
            .catch(function () {
                CFToast('Network error. Please try again.', 'danger');
            })
            .finally(function () {
                if (btn) { btn.disabled = false; btn.innerHTML = original; }
            });
    };

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (form.hasAttribute('data-ajax')) {
            e.preventDefault();
            var handlerName = form.getAttribute('data-ajax-handler');
            if (handlerName && typeof window[handlerName] === 'function') {
                CFSubmit(form, window[handlerName]);
            } else {
                CFSubmit(form);
            }
        }
    });

    /* ---------------- Init ---------------- */
    document.addEventListener('DOMContentLoaded', function () {
        initTheme();
        onScroll();
    });
    window.addEventListener('scroll', onScroll, { passive: true });
})();
