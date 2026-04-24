/**
 * brand_init.js – OpenCMS custom brand bootstrap
 *
 * Runs once after DOM is ready. Adds any runtime branding tweaks that
 * cannot be done in pure CSS (e.g. dynamic title prefix, active-tab
 * highlighting via JS, or app-version badge in the navbar).
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {

        // ── 1. Inject brand badge next to the logo ─────────────────────────
        var brand = document.querySelector('.navbar-brand');
        if (brand && !document.getElementById('oc-brand-badge')) {
            var badge = document.createElement('span');
            badge.id = 'oc-brand-badge';
            badge.textContent = 'OpenCMS';
            badge.style.cssText = [
                'display:inline-block',
                'margin-left:8px',
                'padding:2px 10px',
                'background:linear-gradient(90deg,#1d4ed8,#0ea5e9)',
                'color:#fff',
                'font-size:0.75rem',
                'font-weight:700',
                'letter-spacing:0.08em',
                'border-radius:12px',
                'vertical-align:middle',
                'text-transform:uppercase',
                'user-select:none'
            ].join(';');
            brand.parentNode.insertBefore(badge, brand.nextSibling);
        }

        // ── 2. Highlight the currently active main-menu label ──────────────
        // The appMenu is rendered via Knockout after load; use a short delay.
        setTimeout(function () {
            var menuLabels = document.querySelectorAll('.appMenu > div > .menuSection > .menuLabel');
            menuLabels.forEach(function (lbl) {
                lbl.style.transition = 'background 0.15s ease, color 0.15s ease';
            });
        }, 600);

        // ── 3. Page title prefix ────────────────────────────────────────────
        if (document.title && !document.title.startsWith('OpenCMS')) {
            document.title = 'OpenCMS | ' + document.title;
        }

    });
}());
