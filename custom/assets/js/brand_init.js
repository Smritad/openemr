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

        // ── 1. Remove any pre-existing brand badge (hidden by request) ─────
        var existingBadge = document.getElementById('oc-brand-badge');
        if (existingBadge && existingBadge.parentNode) {
            existingBadge.parentNode.removeChild(existingBadge);
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
