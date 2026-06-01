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

    /**
     * Show a transient toast notification in the top-right corner.
     * Falls back to alert() if DOM isn't ready.
     *
     * Usage:
     *   oeToast({
     *       title: 'Action required',
     *       message: 'Please select a patient first.',
     *       hint: 'Click Patient → New/Search to pick one.',
     *       type: 'warning'   // 'info' (default), 'warning', 'error', 'success'
     *   });
     *   oeToast('Quick message');   // shortcut form
     */
    window.oeToast = function (opts) {
        if (typeof opts === 'string') opts = { message: opts };
        if (!opts || !opts.message) return;

        if (!document.body) {
            try { alert(opts.message); } catch (e) {}
            return;
        }

        var type = opts.type || 'warning';

        // Ensure container exists
        var container = document.getElementById('oe-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'oe-toast-container';
            document.body.appendChild(container);
        }

        var toast = document.createElement('div');
        toast.className = 'oe-toast oe-toast--' + type;

        var iconMap = { info: 'ℹ', warning: '⚠', error: '✖', success: '✔' };
        var icon = iconMap[type] || iconMap.info;

        var html = '<div class="oe-toast__icon">' + icon + '</div>'
            + '<div class="oe-toast__body">';
        if (opts.title) {
            html += '<div class="oe-toast__title"></div>';
        }
        html += '<div class="oe-toast__message"></div>';
        if (opts.hint) {
            html += '<div class="oe-toast__hint"></div>';
        }
        html += '</div>'
            + '<button type="button" class="oe-toast__close" aria-label="Close">×</button>';
        toast.innerHTML = html;

        // Set text content safely (no XSS)
        if (opts.title) toast.querySelector('.oe-toast__title').textContent = opts.title;
        toast.querySelector('.oe-toast__message').textContent = opts.message;
        if (opts.hint) toast.querySelector('.oe-toast__hint').textContent = opts.hint;

        container.appendChild(toast);

        // Fade in
        setTimeout(function () { toast.classList.add('oe-toast--visible'); }, 10);

        // Auto-dismiss
        var ttl = opts.duration || 5000;
        var dismiss = function () {
            toast.classList.remove('oe-toast--visible');
            setTimeout(function () { if (toast.parentNode) toast.parentNode.removeChild(toast); }, 250);
        };
        setTimeout(dismiss, ttl);
        toast.querySelector('.oe-toast__close').addEventListener('click', dismiss);
    };

    /**
     * Global form validation helpers. Any form anywhere in the app can call:
     *
     *   var errors = [];
     *   if (!$('#somefield').val()) {
     *       oeValidate.addFieldError('somefield', 'This field is required');
     *       errors.push('Field X is required');
     *   }
     *   if (errors.length) {
     *       oeValidate.showSummary('#myFormId', errors);
     *       return false;
     *   }
     */
    window.oeValidate = {

        /* Inject a validation-summary banner at the top of a form (idempotent). */
        ensureSummary: function (formSelector) {
            var $form = (window.jQuery || window.$)(formSelector);
            if (!$form.length) return null;
            var $box = $form.find('#oe-validation-summary');
            if ($box.length) return $box;
            $box = (window.jQuery || window.$)(
                '<div id="oe-validation-summary" role="alert" aria-live="polite">' +
                '<h6></h6><ul></ul></div>'
            );
            $form.prepend($box);
            return $box;
        },

        /* Show a list of error messages in the summary box at top of form. */
        showSummary: function (formSelector, messages, heading) {
            var $box = this.ensureSummary(formSelector);
            if (!$box) return;
            $box.find('h6').text(heading || 'Please fix the following before saving:');
            var $ul = $box.find('ul').empty();
            (messages || []).forEach(function (m) {
                $ul.append((window.jQuery || window.$)('<li/>').text(m));
            });
            $box.addClass('show');
            if ($box[0] && $box[0].scrollIntoView) {
                $box[0].scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        },

        /* Clear summary and any inline field error markers in a form. */
        clear: function (formSelector) {
            var $form = (window.jQuery || window.$)(formSelector);
            if (!$form.length) return;
            $form.find('#oe-validation-summary').removeClass('show')
                .find('ul').empty();
            $form.find('.error-message').remove();
            $form.find('.error-border').removeClass('error-border');
        },

        /* Add an inline error span below a specific field + red border on it. */
        addFieldError: function (fieldId, message) {
            var $ = window.jQuery || window.$;
            if (!$) return;
            var $field = $('#' + fieldId);
            if (!$field.length) $field = $('[name="' + fieldId + '"]').first();
            if (!$field.length) return;
            $field.addClass('error-border');
            if (!$field.next('.error-message[data-for="' + fieldId + '"]').length) {
                $field.after('<span class="error-message" data-for="' +
                    fieldId + '">' + message + '</span>');
            }
        }
    };

    /**
     * Global "processing..." button helper.
     * Prevents double-clicks on Save/Submit by disabling the button +
     * showing a spinner+"Processing..." text after the first click.
     *
     * Automatically attaches to:
     *   • <button type="submit">
     *   • <input type="submit">
     *   • Buttons with class "btn-primary"
     *   • Buttons with class "btn-save" / "btn-submit"
     *   • Anything with data-oe-processing="true"
     *
     * Skip a button by adding class "no-processing" or data-oe-processing="false"
     *
     * Auto-restores after 8 seconds in case the submit failed silently
     * (so the button doesn't stay disabled forever).
     */
    function attachProcessingState() {
        var $ = window.jQuery || window.$;
        if (!$) return;

        // Selector for buttons that should get processing state
        var SELECTOR = [
            'button[type="submit"]',
            'input[type="submit"]',
            'button.btn-primary',
            'button.btn-save',
            'a.btn-save',
            'button.btn-submit',
            'a.btn-submit',
            '[data-oe-processing="true"]'
        ].join(',');

        $(document).on('click', SELECTOR, function (e) {
            var btn = this;
            var $btn = $(btn);

            // Skip if disabled, opted out, or part of toolbars we don't manage
            if (btn.disabled || $btn.hasClass('no-processing') ||
                $btn.attr('data-oe-processing') === 'false' ||
                $btn.hasClass('processing-active')) {
                return;
            }

            // Save original label so we can restore later
            var origLabel;
            if (btn.tagName === 'INPUT') {
                origLabel = btn.value;
                btn.setAttribute('data-oe-orig-value', origLabel);
                btn.value = '⏳ ' + (btn.getAttribute('data-oe-loading-text') || 'Processing...');
            } else {
                origLabel = $btn.html();
                btn.setAttribute('data-oe-orig-html', origLabel);
                $btn.html(
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" ' +
                    'style="display:inline-block;width:0.9em;height:0.9em;border:2px solid currentColor;border-right-color:transparent;border-radius:50%;animation:oe-spin 0.75s linear infinite;margin-right:0.4em;vertical-align:middle;"></span>' +
                    (btn.getAttribute('data-oe-loading-text') || 'Processing...')
                );
            }

            // Show the processing state, but DEFER disabling the button.
            // Setting disabled=true synchronously inside a submit button's own
            // click handler makes the browser CANCEL the form submission
            // (nothing saves). Deferring with setTimeout(...,0) lets the native
            // submit/click default fire first, then we disable to prevent
            // double-submit.
            $btn.addClass('processing-active');
            setTimeout(function () { $btn.prop('disabled', true); }, 0);

            // Safety: re-enable after 8 seconds in case form submission stalled
            // (so user isn't stuck with a permanently disabled button)
            setTimeout(function () {
                if ($btn.hasClass('processing-active')) {
                    if (btn.tagName === 'INPUT' && btn.getAttribute('data-oe-orig-value') !== null) {
                        btn.value = btn.getAttribute('data-oe-orig-value');
                    } else if (btn.getAttribute('data-oe-orig-html') !== null) {
                        $btn.html(btn.getAttribute('data-oe-orig-html'));
                    }
                    $btn.removeClass('processing-active').prop('disabled', false);
                }
            }, 8000);
        });

        // Inject the spin keyframe once
        if (!document.getElementById('oe-spinner-style')) {
            var s = document.createElement('style');
            s.id = 'oe-spinner-style';
            s.textContent = '@keyframes oe-spin {to{transform:rotate(360deg);}}' +
                            '.processing-active { cursor: wait !important; opacity: 0.85; }';
            document.head.appendChild(s);
        }
    }

    // Run after DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', attachProcessingState);
    } else {
        attachProcessingState();
    }

    /**
     * Manually reset a button if your form validation failed and you need to
     * re-enable the save button. Call from form validation handlers.
     *
     *   oeResetButton('#form_save');
     */
    window.oeResetButton = function (selector) {
        var $ = window.jQuery || window.$;
        if (!$) return;
        var $btn = $(selector);
        $btn.each(function () {
            var btn = this;
            if (btn.tagName === 'INPUT' && btn.getAttribute('data-oe-orig-value') !== null) {
                btn.value = btn.getAttribute('data-oe-orig-value');
            } else if (btn.getAttribute('data-oe-orig-html') !== null) {
                $(btn).html(btn.getAttribute('data-oe-orig-html'));
            }
            $(btn).removeClass('processing-active').prop('disabled', false);
        });
    };
}());
