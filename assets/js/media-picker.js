/**
 * Reusable Media Library picker modal. Any page that loads this file gets a
 * single global hook:
 *
 *   MediaPicker.open({ multiple: false, csrfToken: '...' }).then(function (items) {
 *       // items is always an array: empty when cancelled, one item in
 *       // single-select mode, N items in bulk-select mode.
 *   });
 *
 * The modal is built and injected into document.body lazily on first use, so
 * no page needs to hand-author any modal markup of its own -- this is what
 * makes it a page-agnostic "hook a button can call" rather than a partial
 * that has to be embedded everywhere it's needed.
 *
 * Item shape (identical to admin/media-library/upload.php's response, and
 * admin/media-library/list.php's, so both code paths hand callers the same
 * thing): { id, category_id, original_filename, mime_type, size, url,
 * created_at }. "url" is always the root-relative
 * /assets/uploads/media-library/<file> path -- the one convention already
 * used everywhere else this app links to a media item.
 */
(function () {
    'use strict';

    var MODAL_ID = 'media-picker-modal';
    var DEFAULT_LIST_URL = '/admin/media-library/list.php';
    var DEFAULT_UPLOAD_URL = '/admin/media-library/upload.php';

    function escapeHtml(value) {
        var div = document.createElement('div');
        div.textContent = value == null ? '' : String(value);
        return div.innerHTML;
    }

    function iconHtml(name, extraClass) {
        return '<i class="bi ' + name + (extraClass ? ' ' + extraClass : '') + '" aria-hidden="true"></i>';
    }

    // Items returned by list.php/upload.php carry a root-relative
    // "/assets/uploads/media-library/..." url, matching the convention used
    // everywhere else this app links to a media item. The picker converts
    // it to absolute right at the point of insertion (not server-side),
    // using the browser's own origin -- so it's always correct for whatever
    // domain the admin is actually editing on (dev or production) with no
    // hardcoded domain or configuration to keep in sync.
    function toAbsoluteUrl(url) {
        if (/^https?:\/\//i.test(url)) {
            return url;
        }
        return window.location.origin + url;
    }

    function debounce(fn, wait) {
        var timer = null;
        return function () {
            var args = arguments;
            window.clearTimeout(timer);
            timer = window.setTimeout(function () { fn.apply(null, args); }, wait);
        };
    }

    var MediaPicker = {
        /**
         * @param {Object} options
         * @param {boolean} [options.multiple=false] Allow selecting more than one item.
         * @param {string} [options.title] Modal heading override.
         * @param {string} [options.listUrl] JSON list endpoint.
         * @param {string} [options.uploadUrl] Upload endpoint (used by the inline "Upload New" dropzone).
         * @param {string} [options.csrfToken] Required for the inline upload dropzone to work.
         * @return {Promise<Array<Object>>}
         */
        open: function (options) {
            options = options || {};
            var self = this;
            this.ensureModal();

            this.multiple = !!options.multiple;
            this.listUrl = options.listUrl || DEFAULT_LIST_URL;
            this.uploadUrl = options.uploadUrl || DEFAULT_UPLOAD_URL;
            this.csrfToken = options.csrfToken || null;
            this.selected = {}; // id => item, preserves selection across filter/search changes
            this.items = [];

            this.titleEl.textContent = options.title || (this.multiple ? 'Select Images' : 'Select an Image');
            this.confirmBtn.textContent = this.multiple ? 'Insert Selected' : 'Insert';
            this.dropzone.classList.toggle('d-none', !this.csrfToken);

            this.categorySelect.value = '';
            this.searchInput.value = '';
            this.updateConfirmState();
            this.fetchAndRender();

            this.bsModal.show();

            return new Promise(function (resolve) {
                self.resolve = resolve;
            });
        },

        ensureModal: function () {
            if (document.getElementById(MODAL_ID)) {
                this.bindExisting();
                return;
            }

            var wrapper = document.createElement('div');
            wrapper.innerHTML = this.modalHtml();
            document.body.appendChild(wrapper.firstElementChild);

            this.bindExisting();
        },

        bindExisting: function () {
            if (this.modalEl) {
                return; // already bound in this page load
            }

            this.modalEl = document.getElementById(MODAL_ID);
            this.titleEl = this.modalEl.querySelector('[data-mp-title]');
            this.categorySelect = this.modalEl.querySelector('[data-mp-category]');
            this.searchInput = this.modalEl.querySelector('[data-mp-search]');
            this.grid = this.modalEl.querySelector('[data-mp-grid]');
            this.emptyState = this.modalEl.querySelector('[data-mp-empty]');
            this.confirmBtn = this.modalEl.querySelector('[data-mp-confirm]');
            this.dropzone = this.modalEl.querySelector('[data-mp-dropzone]');
            this.fileInput = this.modalEl.querySelector('[data-mp-file-input]');
            this.uploadAlerts = this.modalEl.querySelector('[data-mp-upload-alerts]');
            this.bsModal = new window.bootstrap.Modal(this.modalEl);

            var self = this;

            this.categorySelect.addEventListener('change', function () { self.fetchAndRender(); });
            this.searchInput.addEventListener('input', debounce(function () { self.fetchAndRender(); }, 300));

            this.confirmBtn.addEventListener('click', function () {
                var items = Object.keys(self.selected).map(function (id) { return self.selected[id]; });
                self.bsModal.hide();
                if (self.resolve) {
                    self.resolve(items);
                    self.resolve = null;
                }
            });

            this.modalEl.addEventListener('hidden.bs.modal', function () {
                if (self.resolve) {
                    self.resolve([]); // cancelled / dismissed
                    self.resolve = null;
                }
            });

            this.dropzone.addEventListener('click', function () { self.fileInput.click(); });
            this.fileInput.addEventListener('change', function () { self.uploadFiles(self.fileInput.files); });

            ['dragenter', 'dragover'].forEach(function (name) {
                self.dropzone.addEventListener(name, function (e) {
                    e.preventDefault();
                    self.dropzone.classList.add('border-primary');
                });
            });
            ['dragleave', 'drop'].forEach(function (name) {
                self.dropzone.addEventListener(name, function (e) {
                    e.preventDefault();
                    self.dropzone.classList.remove('border-primary');
                });
            });
            this.dropzone.addEventListener('drop', function (e) {
                var files = e.dataTransfer ? e.dataTransfer.files : null;
                if (files && files.length) {
                    self.uploadFiles(files);
                }
            });
        },

        fetchAndRender: function () {
            var self = this;
            var params = new URLSearchParams();
            if (this.categorySelect.value !== '') {
                params.set('category_id', this.categorySelect.value);
            }
            if (this.searchInput.value.trim() !== '') {
                params.set('search', this.searchInput.value.trim());
            }

            this.grid.setAttribute('aria-busy', 'true');

            fetch(this.listUrl + '?' + params.toString())
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    if (!data.success) {
                        return;
                    }
                    self.renderCategoryOptions(data.categories);
                    self.items = data.items.map(function (item) {
                        return Object.assign({}, item, { url: toAbsoluteUrl(item.url) });
                    });
                    self.renderGrid();
                })
                .finally(function () {
                    self.grid.removeAttribute('aria-busy');
                });
        },

        renderCategoryOptions: function (categories) {
            // Preserve the current selection while rebuilding, in case the
            // category list itself changed (e.g. a category was renamed).
            var current = this.categorySelect.value;
            var html = '<option value="">All Categories</option>';
            categories.forEach(function (category) {
                html += '<option value="' + category.id + '">' + escapeHtml(category.name) + '</option>';
            });
            this.categorySelect.innerHTML = html;
            this.categorySelect.value = current;
        },

        renderGrid: function () {
            var self = this;

            this.emptyState.classList.toggle('d-none', this.items.length > 0);

            this.grid.innerHTML = this.items.map(function (item) {
                var isSelected = !!self.selected[item.id];
                return '<div class="col">'
                    + '<div class="media-picker-thumb card h-100' + (isSelected ? ' media-picker-thumb-selected' : '') + '" data-mp-item="' + item.id + '" role="button" tabindex="0">'
                    + '<div class="position-relative">'
                    + '<img src="' + escapeHtml(item.url) + '" class="card-img-top" alt="' + escapeHtml(item.original_filename) + '" loading="lazy">'
                    + '<span class="media-picker-check position-absolute top-0 end-0 m-1">' + iconHtml('bi-check-circle-fill') + '</span>'
                    + '</div>'
                    + '<div class="card-body p-2">'
                    + '<p class="small text-truncate mb-0" title="' + escapeHtml(item.original_filename) + '">' + escapeHtml(item.original_filename) + '</p>'
                    + '</div></div></div>';
            }).join('');

            this.grid.querySelectorAll('[data-mp-item]').forEach(function (el) {
                var select = function () { self.toggleSelect(el.dataset.mpItem); };
                el.addEventListener('click', select);
                el.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        select();
                    }
                });
            });
        },

        toggleSelect: function (id) {
            var item = this.items.find(function (i) { return String(i.id) === String(id); });
            if (!item) {
                return;
            }

            if (this.selected[id]) {
                delete this.selected[id];
            } else {
                if (!this.multiple) {
                    this.selected = {};
                }
                this.selected[id] = item;
            }

            this.renderGrid();
            this.updateConfirmState();
        },

        updateConfirmState: function () {
            var count = Object.keys(this.selected).length;
            this.confirmBtn.disabled = count === 0;
            this.confirmBtn.textContent = this.multiple && count > 0
                ? 'Insert Selected (' + count + ')'
                : (this.multiple ? 'Insert Selected' : 'Insert');
        },

        uploadFiles: function (fileList) {
            var self = this;
            var files = Array.prototype.slice.call(fileList);
            if (!files.length || !this.csrfToken) {
                return;
            }

            this.uploadAlerts.innerHTML = '';
            this.dropzone.setAttribute('aria-busy', 'true');

            var categoryId = this.categorySelect.value;
            var uploadedAny = false;

            files.reduce(function (chain, file) {
                return chain.then(function () {
                    var body = new FormData();
                    body.append('file', file);
                    body.append('category_id', categoryId);
                    body.append('csrf_token', self.csrfToken);

                    return fetch(self.uploadUrl, { method: 'POST', body: body })
                        .then(function (response) { return response.json(); })
                        .then(function (data) {
                            if (!data.success) {
                                var message = data.errors && (data.errors.file || data.errors.category_id);
                                self.showUploadAlert(message || ('Failed to upload "' + file.name + '".'));
                            } else {
                                uploadedAny = true;
                                // Newly uploaded files are immediately usable
                                // without an extra click, matching how
                                // clicking an existing thumbnail behaves.
                                self.selected[data.item.id] = Object.assign({}, data.item, {
                                    url: toAbsoluteUrl(data.item.url),
                                });
                            }
                        })
                        .catch(function () {
                            self.showUploadAlert('Network error while uploading "' + file.name + '".');
                        });
                });
            }, Promise.resolve()).then(function () {
                self.dropzone.removeAttribute('aria-busy');
                self.fileInput.value = '';
                if (uploadedAny) {
                    self.fetchAndRender();
                    self.updateConfirmState();
                }
            });
        },

        showUploadAlert: function (message) {
            var div = document.createElement('div');
            div.className = 'alert alert-danger py-2 px-3 small mb-1';
            div.textContent = message;
            this.uploadAlerts.appendChild(div);
        },

        modalHtml: function () {
            return ''
                + '<div class="modal fade" id="' + MODAL_ID + '" tabindex="-1" aria-hidden="true">'
                + '<div class="modal-dialog modal-lg modal-dialog-scrollable">'
                + '<div class="modal-content">'
                + '<div class="modal-header">'
                + '<h5 class="modal-title" data-mp-title>Select an Image</h5>'
                + '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>'
                + '</div>'
                + '<div class="modal-body">'
                + '<div class="row g-2 align-items-end mb-3">'
                + '<div class="col-auto">'
                + '<label class="form-label small mb-1">Category</label>'
                + '<select class="form-select form-select-sm" data-mp-category><option value="">All Categories</option></select>'
                + '</div>'
                + '<div class="col">'
                + '<label class="form-label small mb-1">Search</label>'
                + '<input type="text" class="form-control form-control-sm" placeholder="Search by filename" data-mp-search>'
                + '</div>'
                + '</div>'
                + '<div class="media-picker-dropzone border border-2 border-dashed rounded p-3 text-center bg-surface-subtle mb-3" data-mp-dropzone role="button" tabindex="0">'
                + '<i class="bi bi-cloud-arrow-up text-muted d-block mb-1"></i>'
                + '<p class="mb-0 small text-muted">Drag &amp; drop to upload a new image, or click to browse</p>'
                + '<input type="file" class="d-none" multiple accept="image/*" data-mp-file-input>'
                + '</div>'
                + '<div data-mp-upload-alerts></div>'
                + '<p class="text-muted text-center py-4 d-none" data-mp-empty>No images found.</p>'
                + '<div class="media-picker-grid row row-cols-3 row-cols-sm-4 row-cols-md-5 g-2" data-mp-grid></div>'
                + '</div>'
                + '<div class="modal-footer">'
                + '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>'
                + '<button type="button" class="btn btn-primary" data-mp-confirm disabled>Insert</button>'
                + '</div>'
                + '</div></div></div>';
        },
    };

    window.MediaPicker = MediaPicker;
})();
