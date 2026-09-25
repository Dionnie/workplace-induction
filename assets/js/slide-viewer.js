/**
 * Slide navigation for the inductee induction page
 * (docs/application/content_blocks_editor.md #5). Every slide is in the
 * page; this shows one at a time and keeps the outline, the counter and the
 * Previous / Next bar in step. On the last slide, Next gives way to the
 * page's finish action (Start Exam / Mark as Complete), when it has one.
 *
 * The current slide is kept in the URL hash (#slide-<id>), so a reload or a
 * shared link opens the same slide. Read-only: the Studio editor
 * (course-editor.js) has its own slide handling.
 */
(function () {
    'use strict';

    var slides = Array.prototype.slice.call(document.querySelectorAll('[data-slide]'));
    if (!slides.length) {
        return;
    }

    var links = Array.prototype.slice.call(document.querySelectorAll('[data-slide-link]'));
    var prevButton = document.querySelector('[data-slide-prev]');
    var nextButton = document.querySelector('[data-slide-next]');
    var finish = document.querySelector('[data-slide-finish]');
    var counter = document.querySelector('[data-slide-counter]');
    var outline = document.getElementById('slide-outline');
    var current = -1;

    function indexOfSlide(id) {
        for (var i = 0; i < slides.length; i++) {
            if (slides[i].dataset.slide === id) {
                return i;
            }
        }
        return -1;
    }

    // Scrolls the outline itself (never the page) so the active link is in
    // view: the sidebar from lg up, the open drawer below that.
    function revealInOutline(link) {
        var scroller = link.closest('.offcanvas.show .offcanvas-body') || link.closest('.cb-slides-sidebar');
        if (!scroller) {
            return;
        }
        // Only the part of the outline on screen counts: before the page is
        // scrolled, the sidebar can extend below the window.
        var box = scroller.getBoundingClientRect();
        var top = Math.max(box.top, 0);
        var bottom = Math.min(box.bottom, window.innerHeight);
        var item = link.getBoundingClientRect();
        if (item.top < top) {
            scroller.scrollTop -= top - item.top + 8;
        } else if (item.bottom > bottom) {
            scroller.scrollTop += item.bottom - bottom + 8;
        }
    }

    function show(index, scrollToSlide) {
        if (index < 0 || index >= slides.length || index === current) {
            return;
        }

        // A hidden video keeps playing; reloading its frame stops it.
        if (current !== -1) {
            slides[current].querySelectorAll('iframe').forEach(function (frame) {
                frame.src = frame.src;
            });
        }

        current = index;
        slides.forEach(function (slide, i) {
            slide.hidden = i !== index;
        });

        var slide = slides[index];
        var id = slide.dataset.slide;
        var isLast = index === slides.length - 1;

        // Start downloading the next slide's images now, so Next feels instant.
        if (slides[index + 1]) {
            slides[index + 1].querySelectorAll('img[loading="lazy"]').forEach(function (img) {
                img.loading = 'eager';
            });
        }

        prevButton.disabled = index === 0;
        nextButton.hidden = isLast && !!finish;
        nextButton.disabled = isLast;
        if (finish) {
            finish.hidden = !isLast;
        }
        counter.innerHTML = '<span class="d-none d-sm-inline">Slide </span>' + (index + 1) + ' of ' + slides.length;

        links.forEach(function (link) {
            var active = link.dataset.slideLink === id;
            link.classList.toggle('active', active);
            if (active) {
                link.setAttribute('aria-current', 'step');
                revealInOutline(link);
            } else {
                link.removeAttribute('aria-current');
            }
        });

        history.replaceState(null, '', '#slide-' + id);

        // When the reader had scrolled down a long slide, start the new one at its top.
        if (scrollToSlide && slide.getBoundingClientRect().top < 0) {
            slide.scrollIntoView({ block: 'start' });
        }
    }

    prevButton.addEventListener('click', function () {
        show(current - 1, true);
    });

    nextButton.addEventListener('click', function () {
        show(current + 1, true);
    });

    links.forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();

            if (outline && window.bootstrap) {
                var drawer = window.bootstrap.Offcanvas.getInstance(outline);
                if (drawer) {
                    drawer.hide();
                }
            }

            var index = indexOfSlide(link.dataset.slideLink);
            if (index === -1) {
                return;
            }
            show(index, true);
            slides[index].querySelector('.cb-slide-title').focus({ preventScroll: true });
        });
    });

    // Below lg the About button is in the outline drawer. Bootstrap handles
    // one overlay at a time, so the drawer closes before the modal opens.
    var aboutButton = document.querySelector('[data-about-open]');
    var about = document.getElementById('induction-about');
    if (aboutButton && about) {
        aboutButton.addEventListener('click', function () {
            var modal = window.bootstrap.Modal.getOrCreateInstance(about);
            var drawer = outline && window.bootstrap.Offcanvas.getInstance(outline);
            if (drawer && outline.classList.contains('show')) {
                outline.addEventListener('hidden.bs.offcanvas', function () {
                    modal.show();
                }, { once: true });
                drawer.hide();
            } else {
                modal.show();
            }
        });
    }

    if (outline) {
        outline.addEventListener('shown.bs.offcanvas', function () {
            var active = outline.querySelector('[data-slide-link].active');
            if (active) {
                revealInOutline(active);
            }
        });
    }

    // Left / Right arrow keys, like a slide deck, unless the key is meant
    // for a field, an open drawer or the About modal.
    document.addEventListener('keydown', function (e) {
        if ((e.key !== 'ArrowLeft' && e.key !== 'ArrowRight') || e.altKey || e.ctrlKey || e.metaKey || e.shiftKey) {
            return;
        }
        if (e.target.closest && e.target.closest('input, textarea, select, [contenteditable="true"], .offcanvas.show, .modal')) {
            return;
        }
        e.preventDefault();
        show(e.key === 'ArrowRight' ? current + 1 : current - 1, true);
    });

    var start = indexOfSlide(decodeURIComponent(window.location.hash.replace(/^#slide-/, '')));
    show(start === -1 ? 0 : start, false);
})();
