/**
 * brand_init.js – MatrixCMS custom brand bootstrap
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

        // ── 3. Replace OpenEMR / OpenCMS in browser tab title with MatrixBricks ──
        if (document.title) {
            document.title = document.title
                .replace(/OpenEMR/gi, 'MatrixBricks')
                .replace(/OpenCMS/gi, 'MatrixBricks');
        }

        // ── 4. Force MatrixBricks favicon (overrides browser cache) ─────────
        document.querySelectorAll('link[rel~="icon"]').forEach(function (el) {
            el.parentNode.removeChild(el);
        });
        var fav = document.createElement('link');
        fav.rel = 'icon';
        fav.type = 'image/x-icon';
        fav.href = '/open_cms/favicon.ico?v=' + Date.now();
        document.head.appendChild(fav);

    });

    /**
     * Global dependency-notice helper.
     * Use this to tell the admin "Action B must be done before Action A".
     *
     * Example:
     *   oeDependencyNotice('#patient-field', 'Select a patient first before booking.');
     *
     *   oeDependencyNotice('#provider-availability', {
     *       message: 'Set provider work hours first under Admin → Users.',
     *       linkText: 'Open Users admin',
     *       linkHref: '/interface/usergroup/usergroup_admin.php'
     *   });
     *
     * Pass null/empty selector to inject at top of <body>.
     */
    window.oeDependencyNotice = function (targetSelector, opts) {
        if (typeof opts === 'string') {
            opts = { message: opts };
        }
        if (!opts || !opts.message) return;

        var notice = document.createElement('div');
        notice.className = 'oe-inline-notice';
        notice.setAttribute('role', 'note');

        var msgSpan = document.createElement('span');
        msgSpan.textContent = opts.message;
        notice.appendChild(msgSpan);

        if (opts.linkHref && opts.linkText) {
            notice.appendChild(document.createTextNode(' '));
            var a = document.createElement('a');
            a.href = opts.linkHref;
            a.textContent = opts.linkText;
            a.style.color = '#cc0000';
            a.style.fontWeight = '600';
            a.style.textDecoration = 'underline';
            notice.appendChild(a);
        }

        var target = targetSelector ? document.querySelector(targetSelector) : null;
        if (target && target.parentNode) {
            target.parentNode.insertBefore(notice, target);
        } else {
            document.body.insertBefore(notice, document.body.firstChild);
        }
        return notice;
    };

    /**
     * Remove dependency notices that were previously injected.
     * Optionally scope to a parent selector.
     */
    window.oeClearDependencyNotices = function (parentSelector) {
        var root = parentSelector ? document.querySelector(parentSelector) : document;
        if (!root) return;
        root.querySelectorAll('.oe-inline-notice').forEach(function (el) {
            el.parentNode.removeChild(el);
        });
    };
}());
