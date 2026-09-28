/**
 * Company Auto-suggest
 *
 * Suggests company names as-is from inductee profiles while preserving
 * full free-form text entry. Features:
 * - Substring and prefix matching (case-insensitive) against existing companies as-is
 * - Top companies display on focus
 * - Keyboard navigation (Arrows, Enter, Tab, Escape)
 * - Free-form typing support (does not restrict input)
 */
(function () {
    'use strict';

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function highlightMatch(text, query) {
        if (!query) return escapeHtml(text);
        var q = query.trim().toLowerCase();
        var lower = text.toLowerCase();
        var idx = lower.indexOf(q);
        if (idx === -1) return escapeHtml(text);

        var before = text.substring(0, idx);
        var match = text.substring(idx, idx + q.length);
        var after = text.substring(idx + q.length);

        return escapeHtml(before) + '<mark>' + escapeHtml(match) + '</mark>' + escapeHtml(after);
    }

    function initAutoSuggest(input) {
        var wrapper = input.closest('[data-company-autosuggest-wrapper]') || input.parentElement;
        if (!wrapper) return;

        // Load data from embedded JSON or fallback to datalist options
        var suggestions = [];
        var dataScript = document.getElementById('company-suggestions-data');
        if (dataScript) {
            try {
                suggestions = JSON.parse(dataScript.textContent);
            } catch (e) {
                suggestions = [];
            }
        }

        if (!suggestions.length) {
            var datalistId = input.getAttribute('list');
            if (datalistId) {
                var datalist = document.getElementById(datalistId);
                if (datalist) {
                    var options = datalist.querySelectorAll('option');
                    options.forEach(function (opt) {
                        suggestions.push({
                            company: opt.value || opt.textContent,
                            count: 1
                        });
                    });
                }
            }
        }

        if (!suggestions.length) return;

        // Remove native datalist attribute to prevent double dropdowns in Chrome/WebKit
        if (input.hasAttribute('list')) {
            input.setAttribute('data-original-list', input.getAttribute('list'));
            input.removeAttribute('list');
        }

        // Store companies as-is
        var list = suggestions.map(function (item) {
            var name = typeof item === 'string' ? item : item.company;
            var count = typeof item === 'object' && item.count ? item.count : 0;
            return {
                company: name,
                lower: name.toLowerCase(),
                count: count
            };
        });

        // Find or create dropdown menu container
        var menu = wrapper.querySelector('.company-suggestions-menu');
        if (!menu) {
            menu = document.createElement('div');
            menu.className = 'dropdown-menu shadow-sm w-100 company-suggestions-menu';
            menu.style.display = 'none';
            menu.setAttribute('role', 'listbox');
            menu.setAttribute('aria-label', 'Company suggestions');
            wrapper.appendChild(menu);
        }

        var activeIndex = -1;
        var currentItems = [];

        function closeMenu() {
            menu.style.display = 'none';
            menu.innerHTML = '';
            activeIndex = -1;
            currentItems = [];
            input.removeAttribute('aria-activedescendant');
        }

        function selectItem(company) {
            input.value = company;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
            closeMenu();
            input.focus();
        }

        function updateActiveDescendant() {
            var buttons = menu.querySelectorAll('.dropdown-item');
            buttons.forEach(function (btn, idx) {
                var isActive = idx === activeIndex;
                btn.classList.toggle('active', isActive);
                btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
                if (isActive) {
                    btn.scrollIntoView({ block: 'nearest' });
                    input.setAttribute('aria-activedescendant', btn.id || ('comp-opt-' + idx));
                }
            });
        }

        function renderMenu(items, query, isPopular) {
            currentItems = items;
            activeIndex = -1;

            if (!items.length) {
                if (query.trim().length > 0) {
                    menu.innerHTML = '<div class="dropdown-item-text text-muted small py-2 px-3">' +
                        '<i class="bi bi-info-circle me-1 text-primary"></i>Press Enter or continue typing to use &ldquo;<strong>' +
                        escapeHtml(query.trim()) + '</strong>&rdquo;' +
                        '</div>';
                    menu.style.display = 'block';
                } else {
                    closeMenu();
                }
                return;
            }

            var html = '';
            if (isPopular) {
                html += '<div class="dropdown-header text-uppercase small text-muted py-1 px-3">Top Companies</div>';
            }

            items.forEach(function (item, idx) {
                var btnId = 'comp-opt-' + idx;
                var companyName = item.company;
                var displayName = highlightMatch(companyName, query);

                html += '<button type="button" class="dropdown-item d-flex justify-content-between align-items-center py-2 px-3" ' +
                    'id="' + btnId + '" role="option" aria-selected="false" data-company-name="' + escapeHtml(companyName) + '">';
                
                html += '<div class="text-truncate me-2">' +
                    '<div>' + displayName + '</div>' +
                    '</div>';

                if (item.count > 0) {
                    html += '<span class="badge text-bg-light border text-muted ms-auto flex-shrink-0">' +
                        item.count + ' ' + (item.count === 1 ? 'inductee' : 'inductees') +
                        '</span>';
                }

                html += '</button>';
            });

            menu.innerHTML = html;
            menu.style.display = 'block';

            // Click handlers on items
            var buttons = menu.querySelectorAll('.dropdown-item');
            buttons.forEach(function (btn) {
                btn.addEventListener('mousedown', function (e) {
                    // mousedown prevents blur before click
                    e.preventDefault();
                    var comp = btn.getAttribute('data-company-name');
                    if (comp) selectItem(comp);
                });
            });
        }

        function filterSuggestions(query) {
            var q = query.trim().toLowerCase();
            if (!q) {
                // Show top 7 popular companies as-is
                var popular = list.slice(0, 7);
                renderMenu(popular, '', true);
                return;
            }

            var matches = [];
            list.forEach(function (item) {
                var score = -1;

                // 1. Exact match
                if (item.lower === q) {
                    score = 100;
                }
                // 2. Starts with query
                else if (item.lower.indexOf(q) === 0) {
                    score = 80;
                }
                // 3. Substring match
                else {
                    var subIdx = item.lower.indexOf(q);
                    if (subIdx !== -1) {
                        score = 50 - subIdx;
                    }
                }

                if (score >= 0) {
                    matches.push({
                        company: item.company,
                        count: item.count,
                        score: score
                    });
                }
            });

            // Sort: highest score first, then highest count first, then alphabetical
            matches.sort(function (a, b) {
                if (b.score !== a.score) return b.score - a.score;
                if (b.count !== a.count) return b.count - a.count;
                return a.company.localeCompare(b.company);
            });

            renderMenu(matches.slice(0, 8), query, false);
        }

        // Input events
        input.addEventListener('input', function () {
            filterSuggestions(input.value);
        });

        input.addEventListener('focus', function () {
            filterSuggestions(input.value);
        });

        input.addEventListener('keydown', function (e) {
            if (menu.style.display !== 'block') {
                if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                    filterSuggestions(input.value);
                    e.preventDefault();
                }
                return;
            }

            var buttons = menu.querySelectorAll('.dropdown-item');
            if (!buttons.length) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIndex = (activeIndex + 1) % buttons.length;
                updateActiveDescendant();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIndex = activeIndex <= 0 ? buttons.length - 1 : activeIndex - 1;
                updateActiveDescendant();
            } else if (e.key === 'Enter') {
                if (activeIndex >= 0 && buttons[activeIndex]) {
                    e.preventDefault();
                    var comp = buttons[activeIndex].getAttribute('data-company-name');
                    if (comp) selectItem(comp);
                }
                // If no item is active, let normal Enter occur (free-form submission)
            } else if (e.key === 'Tab') {
                if (activeIndex >= 0 && buttons[activeIndex]) {
                    var comp = buttons[activeIndex].getAttribute('data-company-name');
                    if (comp) {
                        input.value = comp;
                    }
                }
                closeMenu();
            } else if (e.key === 'Escape') {
                e.preventDefault();
                closeMenu();
            }
        });

        // Close on blur or click outside
        document.addEventListener('click', function (e) {
            if (!wrapper.contains(e.target)) {
                closeMenu();
            }
        });
    }

    // Auto-initialize on DOM ready
    function init() {
        document.querySelectorAll('input[data-company-autosuggest]').forEach(initAutoSuggest);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
