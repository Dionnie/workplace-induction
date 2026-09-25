/**
 * App-wide UI behaviour, loaded on every page by views/partials/scripts.php.
 * See docs/core/design-system.html#loading.
 */
(function () {
    'use strict';

    // Loading state: a submitted POST form disables the button that sent it
    // and shows a spinner, so a slow request can't be submitted twice.
    // Opt a button out with data-no-loading.
    document.addEventListener('submit', function (event) {
        var button = event.submitter;
        if (event.defaultPrevented || event.target.method !== 'post'
            || !button || button.hasAttribute('data-no-loading')) {
            return;
        }

        // Wait until the browser has collected the form data, so a named
        // submit button still sends its value.
        setTimeout(function () {
            button.disabled = true;
            button.setAttribute('data-loading', '');
            button.insertAdjacentHTML('afterbegin', '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>');
        }, 0);
    });

    // Bootstrap tooltips are opt-in: start every one on the page
    // (field help, .field-help).
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (element) {
        bootstrap.Tooltip.getOrCreateInstance(element);
    });

    // Going Back can restore the page from the browser cache with the
    // button still disabled; put it back.
    window.addEventListener('pageshow', function (event) {
        if (!event.persisted) {
            return;
        }
        document.querySelectorAll('[data-loading]').forEach(function (button) {
            button.disabled = false;
            button.removeAttribute('data-loading');
            var spinner = button.querySelector('.spinner-border');
            if (spinner) {
                spinner.remove();
            }
        });
    });
})();
