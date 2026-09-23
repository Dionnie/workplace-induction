/**
 * Course outline teleport scroll for the inductee-facing induction page.
 * See docs/application/content_blocks_editor.md #9. Read-only: no editing,
 * no block CRUD — kept separate from course-editor.js (the Studio editor)
 * since this page never edits content, matching this project's convention
 * of small, single-purpose JS files.
 */
(function () {
    'use strict';

    document.addEventListener('click', function (e) {
        var link = e.target.closest('[data-teleport]');
        if (!link) {
            return;
        }

        var target = document.getElementById(link.dataset.teleport);
        if (!target) {
            return;
        }

        e.preventDefault();

        // Close the offcanvas drawer first (if the link was clicked from
        // inside it) so it doesn't obscure the scroll.
        var offcanvasEl = link.closest('.offcanvas');
        if (offcanvasEl && window.bootstrap) {
            var instance = window.bootstrap.Offcanvas.getInstance(offcanvasEl);
            if (instance) {
                instance.hide();
            }
        }

        target.scrollIntoView({ behavior: 'smooth', block: 'center' });

        // Force a reflow between remove/add so the pulse re-triggers even
        // when the same link is clicked twice in a row.
        target.classList.remove('teleport-highlight-pulse');
        void target.offsetWidth;
        target.classList.add('teleport-highlight-pulse');
    });
})();
