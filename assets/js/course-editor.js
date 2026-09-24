/**
 * Content Blocks Studio editor. See docs/application/content_blocks_editor.md #7.
 *
 * Single-active-block WYSIWYG editing: only the selected block renders its
 * edit fields; every other block renders its inductee-facing preview markup
 * inline in the same canvas. The active block's own fields are styled to
 * already look like the final result (heading-sized title input, live
 * image/video/alert preview) so editing IS the preview, not a separate form.
 * Server-side (ContentBlockRenderer) renders the same block types for the
 * inductee page; this file's preview markup is a deliberate, independent
 * duplicate for live client-side editing, matching this project's pattern
 * of parallel client/server implementations (exam-editor.js does the same)
 * rather than a network round trip per edit.
 */
(function () {
    'use strict';

    var ALERT_VARIANTS = ['info', 'warning', 'danger', 'success'];
    var IMAGE_WIDTHS = ['full', 'content', 'small', 'smaller'];
    var IMAGE_ALIGNS = ['left', 'center', 'right'];
    var IMAGE_SHAPES = ['rounded', 'square', 'circle', 'as-is'];
    var IMAGE_ASPECTS = ['natural', '16-9', '4-3', '1-1'];
    var IFRAME_ASPECT_RATIOS = ['16:9', '4:3'];
    var TEXT_BASED_TYPES = ['section', 'lecture', 'text', 'alert'];

    // Bootstrap Icons (loaded by views/admin/inductions/editor.php) — used
    // throughout the Studio editor for quick visual scanning; never relied
    // on as the only cue (every icon button still carries a title/label).
    var BLOCK_ICONS = {
        section: 'bi-bookmark',
        lecture: 'bi-journal-text',
        text: 'bi-text-paragraph',
        alert: 'bi-exclamation-triangle',
        image: 'bi-image',
        gallery: 'bi-images',
        iframe: 'bi-camera-video',
        raw_html: 'bi-code-slash',
    };
    var ALERT_ICONS = { info: 'bi-info-circle', warning: 'bi-exclamation-triangle', danger: 'bi-exclamation-octagon', success: 'bi-check-circle' };
    var BLOCK_LABELS = { section: 'Section', lecture: 'Lecture', text: 'Text', alert: 'Alert', image: 'Image', gallery: 'Gallery', iframe: 'Video', raw_html: 'Raw HTML' };
    var GALLERY_COLUMNS = [2, 3, 4];

    /**
     * Bootstrap `row-cols-*` classes (docs/core/ui-guidelines.md "prefer
     * Bootstrap grid") so gallery images wrap into further rows
     * automatically past the chosen column count — no custom CSS grid.
     * Mirrors ContentBlockRenderer::galleryRowClasses(), the authoritative
     * server-side copy.
     */
    function galleryRowClasses(columns) {
        switch (parseInt(columns, 10)) {
            case 2: return 'row-cols-1 row-cols-sm-2';
            case 4: return 'row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4';
            default: return 'row-cols-1 row-cols-sm-2 row-cols-md-3';
        }
    }

    function escapeHtml(value) {
        var div = document.createElement('div');
        div.textContent = value == null ? '' : String(value);
        return div.innerHTML;
    }

    function iconHtml(name, extraClass) {
        return '<i class="bi ' + name + (extraClass ? ' ' + extraClass : '') + '" aria-hidden="true"></i>';
    }

    // A plain YouTube watch/share/shorts URL cannot be used as an <iframe>
    // src — YouTube blocks it from framing. Rewrite recognized YouTube URL
    // shapes to the dedicated embed URL; anything else (Vimeo, other
    // providers, already-embed URLs) passes through unchanged. Mirrored
    // server-side in ContentBlockService, which is the authoritative copy —
    // this one is only for instant preview feedback while typing.
    function formatEmbedUrl(url) {
        var patterns = [
            /^https?:\/\/(?:www\.)?youtu\.be\/([A-Za-z0-9_-]{11})/i,
            /^https?:\/\/(?:www\.)?youtube\.com\/watch\?(?:.*&)?v=([A-Za-z0-9_-]{11})/i,
            /^https?:\/\/(?:www\.)?youtube\.com\/shorts\/([A-Za-z0-9_-]{11})/i,
        ];

        for (var i = 0; i < patterns.length; i++) {
            var match = patterns[i].exec(url);
            if (match) {
                return 'https://www.youtube-nocookie.com/embed/' + match[1];
            }
        }

        return url;
    }

    // A small badge above section/lecture titles so the two structural
    // block types stay identifiable at a glance now that the canvas has no
    // visual nesting/indentation to signal hierarchy.
    function typeBadgeHtml(type) {
        if (type !== 'section' && type !== 'lecture') {
            return '';
        }
        return '<span class="cb-type-badge cb-type-badge-' + type + '">'
            + iconHtml(BLOCK_ICONS[type], 'me-1') + BLOCK_LABELS[type] + '</span>';
    }

    function generateId(prefix) {
        return prefix + '_' + Math.random().toString(36).slice(2, 10);
    }

    function selectHtml(field, options, labels, current) {
        var html = '<select class="cb-settings-select form-select form-select-sm" data-field="' + field + '">';
        options.forEach(function (value) {
            var selected = value === current ? ' selected' : '';
            html += '<option value="' + value + '"' + selected + '>' + (labels[value] || value) + '</option>';
        });
        return html + '</select>';
    }

    function richTextToolbarHtml() {
        return ''
            + '<div class="cb-richtext-toolbar btn-toolbar" role="toolbar">'
            + '<div class="cb-richtext-toolbar-group btn-group btn-group-sm me-1">'
            + '<button type="button" class="cb-richtext-btn btn btn-outline-secondary" data-cmd="bold" title="Bold">' + iconHtml('bi-type-bold') + '</button>'
            + '<button type="button" class="cb-richtext-btn btn btn-outline-secondary" data-cmd="italic" title="Italic">' + iconHtml('bi-type-italic') + '</button>'
            + '<button type="button" class="cb-richtext-btn btn btn-outline-secondary" data-cmd="underline" title="Underline">' + iconHtml('bi-type-underline') + '</button>'
            + '<button type="button" class="cb-richtext-btn btn btn-outline-secondary" data-cmd="strikeThrough" title="Strikethrough">' + iconHtml('bi-type-strikethrough') + '</button>'
            + '</div>'
            + '<div class="cb-richtext-toolbar-group btn-group btn-group-sm me-1">'
            + '<button type="button" class="cb-richtext-btn btn btn-outline-secondary" data-cmd-block="p" title="Paragraph">' + iconHtml('bi-paragraph') + '</button>'
            + '<button type="button" class="cb-richtext-btn btn btn-outline-secondary" data-cmd-block="blockquote" title="Quote">' + iconHtml('bi-quote') + '</button>'
            + '</div>'
            + '<div class="cb-richtext-toolbar-group btn-group btn-group-sm me-1">'
            + '<button type="button" class="cb-richtext-btn btn btn-outline-secondary" data-cmd="insertUnorderedList" title="Bullet list">' + iconHtml('bi-list-ul') + '</button>'
            + '<button type="button" class="cb-richtext-btn btn btn-outline-secondary" data-cmd="insertOrderedList" title="Numbered list">' + iconHtml('bi-list-ol') + '</button>'
            + '</div>'
            + '<div class="cb-richtext-toolbar-group btn-group btn-group-sm">'
            + '<button type="button" class="cb-richtext-btn btn btn-outline-secondary" data-cmd="removeFormat" title="Clear formatting">' + iconHtml('bi-eraser') + '</button>'
            + '</div>'
            + '</div>';
    }

    function richTextEditHtml(field, value, blockId, placeholder, extraClass) {
        return richTextToolbarHtml()
            + '<div class="cb-richtext' + (extraClass ? ' ' + extraClass : '') + '" contenteditable="true" '
            + 'data-field="' + field + '" data-block-id="' + blockId + '" data-placeholder="' + escapeHtml(placeholder || '') + '">'
            + (value || '')
            + '</div>';
    }

    var CourseEditor = {
        init: function (options) {
            this.container = document.querySelector(options.blocksContainer);
            this.emptyHint = document.querySelector(options.emptyHint);

            // The outline renders into two places at once — the floating
            // rail (wide viewports) and the offcanvas drawer (everywhere
            // else) — kept in sync from the same data so whichever one is
            // visible is always current.
            this.outlineLists = [options.outlineList, options.outlineListOffcanvas]
                .filter(Boolean).map(function (sel) { return document.querySelector(sel); }).filter(Boolean);
            this.outlineEmpties = [options.outlineEmpty, options.outlineEmptyOffcanvas]
                .filter(Boolean).map(function (sel) { return document.querySelector(sel); }).filter(Boolean);
            this.offcanvasEl = options.offcanvas ? document.querySelector(options.offcanvas) : null;

            this.saveButton = document.querySelector(options.saveButton);
            this.saveStatus = document.querySelector(options.saveStatus);
            this.saveUrl = options.saveUrl;
            this.csrfToken = options.csrfToken;

            if (!this.container) {
                return;
            }

            this.blocks = [];
            this.activeBlockId = null;
            this.focusedBlockId = null;
            this.dirty = false;

            var initial = this.container.dataset.initial;
            if (initial) {
                try {
                    var parsed = JSON.parse(initial);
                    if (Array.isArray(parsed)) {
                        this.blocks = parsed.map(function (block) {
                            block.id = block.id || generateId('blk');
                            return block;
                        });
                    }
                } catch (e) {
                    this.blocks = [];
                }
            }

            this.render();
            this.attachGlobalEvents();
        },

        defaultsFor: function (type) {
            // Fields start empty and rely on placeholders, not prefilled
            // sample text, so the admin's own words are never mistaken for
            // already-authored content.
            switch (type) {
                case 'section':
                    return { title: '', description: '' };
                case 'lecture':
                    return { title: '', content: '' };
                case 'text':
                    return { content: '' };
                case 'alert':
                    return { variant: 'info', title: '', content: '' };
                case 'image':
                    return { url: '', caption: '', width: 'content', align: 'center', shape: 'as-is', aspect: '4-3' };
                case 'gallery':
                    return { columns: 3, images: [] };
                case 'iframe':
                    return { url: '', aspect_ratio: '16:9', caption: '' };
                case 'raw_html':
                    return { content: '' };
                default:
                    return {};
            }
        },

        addBlock: function (type, afterId) {
            this.syncActiveBlockData();

            var block = Object.assign({ id: generateId('blk'), type: type }, this.defaultsFor(type));
            var targetId = afterId || this.activeBlockId || this.focusedBlockId;
            var index = targetId ? this.blocks.findIndex(function (b) { return b.id === targetId; }) : -1;

            if (index !== -1) {
                this.blocks.splice(index + 1, 0, block);
            } else {
                this.blocks.push(block);
            }

            this.activeBlockId = block.id;
            this.focusedBlockId = null;
            this.markDirty();
            this.render();
            this.focusActiveField();
        },

        removeBlock: function (id) {
            if (!window.confirm('Remove this block?')) {
                return;
            }

            this.blocks = this.blocks.filter(function (b) { return b.id !== id; });
            if (this.activeBlockId === id) {
                this.activeBlockId = null;
            }
            if (this.focusedBlockId === id) {
                this.focusedBlockId = null;
            }

            this.markDirty();
            this.render();
        },

        moveBlock: function (id, direction) {
            this.syncActiveBlockData();

            var index = this.blocks.findIndex(function (b) { return b.id === id; });
            if (index === -1) {
                return;
            }

            var swapWith = direction === 'up' ? index - 1 : index + 1;
            if (swapWith < 0 || swapWith >= this.blocks.length) {
                return;
            }

            var temp = this.blocks[index];
            this.blocks[index] = this.blocks[swapWith];
            this.blocks[swapWith] = temp;

            this.activeBlockId = null;
            this.focusedBlockId = id;
            this.markDirty();
            this.render();
        },

        duplicateBlock: function (id) {
            this.syncActiveBlockData();

            var index = this.blocks.findIndex(function (b) { return b.id === id; });
            if (index === -1) {
                return;
            }

            var clone = JSON.parse(JSON.stringify(this.blocks[index]));
            clone.id = generateId('blk');
            this.blocks.splice(index + 1, 0, clone);

            this.activeBlockId = null;
            this.focusedBlockId = clone.id;
            this.markDirty();
            this.render();
        },

        setVariant: function (id, variant) {
            var block = this.blocks.find(function (b) { return b.id === id; });
            if (block) {
                block.variant = variant;
                this.markDirty();
                this.render();
            }
        },

        setActiveBlock: function (id) {
            if (this.activeBlockId === id) {
                return;
            }

            this.syncActiveBlockData();
            this.activeBlockId = id;
            this.focusedBlockId = null;
            this.render();
            this.focusActiveField();
        },

        deselect: function () {
            if (!this.activeBlockId) {
                return;
            }

            this.syncActiveBlockData();
            this.activeBlockId = null;
            this.render();
        },

        focusActiveField: function () {
            var self = this;
            window.setTimeout(function () {
                var wrapper = self.container.querySelector('.cb-block.active');
                if (!wrapper) {
                    return;
                }
                // Title/content fields (heading-styled inputs, rich text) are
                // the natural first field to land on. Image/iframe blocks
                // have neither, so this falls through to the first
                // [data-field] in DOM order, which is their URL input — the
                // caption field is deliberately plain form-control styling
                // (not .cb-title-input) so it's never mistaken for that
                // first field.
                var field = wrapper.querySelector('.cb-title-input, .cb-richtext') || wrapper.querySelector('[data-field]');
                if (field) {
                    field.focus();
                }
            }, 20);
        },

        syncActiveBlockData: function () {
            if (!this.activeBlockId) {
                return;
            }

            var wrapper = this.container.querySelector('.cb-block[data-id="' + this.activeBlockId + '"]');
            var block = this.blocks.find((b) => b.id === this.activeBlockId);
            if (!wrapper || !block) {
                return;
            }

            wrapper.querySelectorAll('[data-field]').forEach(function (field) {
                var name = field.dataset.field;
                if (field.isContentEditable) {
                    block[name] = field.innerHTML;
                } else {
                    block[name] = field.value;
                }
            });
        },

        markDirty: function () {
            this.dirty = true;
            if (this.saveStatus) {
                this.saveStatus.className = 'badge text-bg-warning';
                this.saveStatus.textContent = 'Unsaved changes';
            }
        },

        markSaved: function () {
            this.dirty = false;
            if (this.saveStatus) {
                this.saveStatus.className = 'badge text-bg-success';
                this.saveStatus.textContent = 'Saved';
            }
        },

        save: function () {
            this.syncActiveBlockData();

            if (this.saveButton) {
                this.saveButton.disabled = true;
                this.saveButton.innerHTML = iconHtml('bi-arrow-repeat', 'me-1 cb-spin') + 'Saving...';
            }

            var body = new URLSearchParams();
            body.set('csrf_token', this.csrfToken);
            body.set('content_blocks', JSON.stringify(this.blocks));

            var self = this;
            fetch(this.saveUrl, { method: 'POST', body: body })
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    if (data.success) {
                        self.markSaved();
                    } else {
                        window.alert('Error saving: ' + data.message);
                    }
                })
                .catch(function () {
                    window.alert('Network error while saving.');
                })
                .finally(function () {
                    if (self.saveButton) {
                        self.saveButton.disabled = false;
                        self.saveButton.innerHTML = iconHtml('bi-check2-circle', 'me-1') + 'Save Changes';
                    }
                });
        },

        attachGlobalEvents: function () {
            var self = this;

            if (this.saveButton) {
                this.saveButton.addEventListener('click', function () { self.save(); });
            }

            window.addEventListener('keydown', function (e) {
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
                    e.preventDefault();
                    self.save();
                }
            });

            // Leaving with unsaved changes asks first.
            window.addEventListener('beforeunload', function (e) {
                if (self.dirty) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });

            document.addEventListener('click', function (e) {
                if (e.target.closest('.cb-block') || e.target.closest('.cb-quick-insert')) {
                    return;
                }
                self.deselect();
            });

            // Keep contenteditable selection alive when a toolbar button is clicked.
            document.addEventListener('mousedown', function (e) {
                if (e.target.closest('.cb-richtext-toolbar')) {
                    e.preventDefault();
                }
            });

            document.addEventListener('click', function (e) {
                var cmdBtn = e.target.closest('[data-cmd]');
                if (cmdBtn) {
                    document.execCommand(cmdBtn.dataset.cmd, false, null);
                    self.markDirty();
                    return;
                }

                var blockBtn = e.target.closest('[data-cmd-block]');
                if (blockBtn) {
                    document.execCommand('formatBlock', false, blockBtn.dataset.cmdBlock);
                    self.markDirty();
                }
            });

            document.addEventListener('input', function (e) {
                if (e.target.matches('.cb-richtext[data-field]')) {
                    self.markDirty();
                }
            });

            // Course outline teleport scroll — mirrors course-outline.js's
            // behavior on the inductee page (same pulse re-trigger via
            // remove/reflow/add) so both surfaces feel identical.
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

                // Close the offcanvas drawer first (if the link was clicked
                // from inside it) so it doesn't obscure the scroll.
                if (self.offcanvasEl && self.offcanvasEl.contains(link) && window.bootstrap) {
                    var instance = window.bootstrap.Offcanvas.getInstance(self.offcanvasEl);
                    if (instance) {
                        instance.hide();
                    }
                }

                target.scrollIntoView({ behavior: 'smooth', block: 'center' });

                target.classList.remove('teleport-highlight-pulse');
                void target.offsetWidth;
                target.classList.add('teleport-highlight-pulse');
            });
        },

        render: function () {
            this.container.innerHTML = '';

            var self = this;
            this.blocks.forEach(function (block, index) {
                var wrapper = self.blockElement(block, index);
                self.container.appendChild(wrapper);

                if (block.id === self.activeBlockId || block.id === self.focusedBlockId) {
                    self.insertQuickInsertBar(wrapper);
                }
            });

            if (!this.activeBlockId && !this.focusedBlockId) {
                this.insertQuickInsertBar(null);
            }

            if (this.emptyHint) {
                this.emptyHint.style.display = this.blocks.length === 0 ? '' : 'none';
            }

            this.bindBlockEvents();
            this.renderOutline();
        },

        /**
         * Live course-structure outline, mirroring CourseOutlineBuilder.php's
         * logic client-side (a deliberate parallel implementation, not a
         * network round trip — the same pattern exam-editor.js follows
         * with ExamService). Every "section" block
         * starts a new node; only "lecture" blocks nest under it as items —
         * every other block type is canvas content, not a navigable outline
         * item. A lecture appearing before any section gets an implicit
         * leading section rather than being dropped.
         */
        buildOutline: function () {
            var sections = [];
            var currentIndex = null;
            var sectionNumber = 0;

            this.blocks.forEach(function (block) {
                if (block.type === 'section') {
                    sectionNumber++;
                    sections.push({ id: block.id, title: block.title || ('Section ' + sectionNumber), items: [] });
                    currentIndex = sections.length - 1;
                    return;
                }

                if (block.type !== 'lecture') {
                    return;
                }

                if (currentIndex === null) {
                    sections.push({ id: 'section-overview', title: 'Section 1: Course Overview', items: [] });
                    currentIndex = sections.length - 1;
                }

                sections[currentIndex].items.push({ id: block.id, label: block.title || 'Lecture' });
            });

            return sections;
        },

        renderOutline: function () {
            if (this.outlineLists.length === 0) {
                return;
            }

            var outline = this.buildOutline();

            var html = outline.map(function (section) {
                var items = section.items.map(function (item) {
                    return '<li><a href="#block-' + item.id + '" class="cb-outline-link" data-teleport="block-' + item.id + '">'
                        + escapeHtml(item.label) + '</a></li>';
                }).join('');

                return '<li class="cb-outline-section">'
                    + '<a href="#block-' + section.id + '" class="cb-outline-link cb-outline-link-section" data-teleport="block-' + section.id + '">'
                    + escapeHtml(section.title) + '</a>'
                    + (items ? '<ul class="cb-outline-items list-unstyled">' + items + '</ul>' : '')
                    + '</li>';
            }).join('');

            this.outlineLists.forEach(function (list) { list.innerHTML = html; });
            this.outlineEmpties.forEach(function (empty) {
                empty.style.display = outline.length === 0 ? '' : 'none';
            });
        },

        insertQuickInsertBar: function (afterEl) {
            var bar = document.createElement('div');
            bar.className = 'cb-quick-insert d-flex flex-wrap gap-2 justify-content-center py-3 my-3 border border-dashed rounded';

            var types = ['section', 'lecture', 'text', 'alert', 'image', 'gallery', 'iframe', 'raw_html'];
            var self = this;
            bar.innerHTML = types.map(function (type) {
                return '<button type="button" class="cb-add-block-btn btn btn-outline-secondary" data-quick-add="' + type + '">'
                    + iconHtml(BLOCK_ICONS[type], 'me-1') + BLOCK_LABELS[type] + '</button>';
            }).join('');

            bar.querySelectorAll('[data-quick-add]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    self.addBlock(btn.dataset.quickAdd, afterEl ? afterEl.dataset.id : null);
                });
            });

            if (afterEl) {
                afterEl.after(bar);
            } else {
                this.container.appendChild(bar);
            }
        },

        blockElement: function (block, index) {
            var isActive = block.id === this.activeBlockId;
            var isFocused = !isActive && block.id === this.focusedBlockId;

            var wrapper = document.createElement('div');
            wrapper.className = 'cb-block' + (isActive ? ' active' : '') + (isFocused ? ' focused' : '');
            wrapper.id = 'block-' + block.id;
            wrapper.dataset.id = block.id;
            wrapper.dataset.type = block.type;

            // Floating, borderless, hover-only controls: they overlay the
            // canvas instead of reserving their own row, so the WYSIWYG
            // canvas isn't disturbed by editor chrome (see .cb-block-controls
            // in app.css for the hover/opacity behavior).
            var controls = '<div class="cb-block-controls">'
                + '<button type="button" class="cb-block-control-btn btn" data-move="up" title="Move up" ' + (index === 0 ? 'disabled' : '') + '>' + iconHtml('bi-arrow-up') + '</button>'
                + '<button type="button" class="cb-block-control-btn btn" data-move="down" title="Move down" ' + (index === this.blocks.length - 1 ? 'disabled' : '') + '>' + iconHtml('bi-arrow-down') + '</button>'
                + '<button type="button" class="cb-block-control-btn btn" data-duplicate title="Duplicate">' + iconHtml('bi-copy') + '</button>'
                + '<button type="button" class="cb-block-control-btn btn" data-remove title="Remove">' + iconHtml('bi-trash') + '</button>'
                + '</div>';

            wrapper.innerHTML = controls + '<div class="cb-block-body">' + this.renderBlockBody(block, isActive) + '</div>';

            return wrapper;
        },

        renderBlockBody: function (block, isActive) {
            var body = isActive ? this.editHtml(block) : this.previewHtml(block);

            // Text-based blocks preview/edit at the 700px reading width, same
            // as ContentBlockRenderer on the inductee page, so the Studio
            // canvas is a true WYSIWYG match. Media blocks use the full
            // 1024px canvas.
            if (TEXT_BASED_TYPES.indexOf(block.type) !== -1) {
                body = '<div class="content-block-inner">' + body + '</div>';
            }

            return body;
        },

        editHtml: function (block) {
            switch (block.type) {
                case 'section':
                    return typeBadgeHtml('section')
                        + '<input type="text" class="cb-title-input cb-heading-2" data-field="title" placeholder="Untitled Section" value="' + escapeHtml(block.title) + '">'
                        + richTextEditHtml('description', block.description, block.id, 'Add a short description (optional)');
                case 'lecture':
                    return typeBadgeHtml('lecture')
                        + '<input type="text" class="cb-title-input cb-heading-3" data-field="title" placeholder="Untitled Lecture" value="' + escapeHtml(block.title) + '">'
                        + richTextEditHtml('content', block.content, block.id, 'Write the lecture content...');
                case 'text':
                    return richTextEditHtml('content', block.content, block.id, 'Start writing...');
                case 'alert':
                    return this.alertVariantButtons(block)
                        + '<div class="cb-alert-box alert alert-' + block.variant + ' mb-0">'
                        + '<input type="text" class="cb-title-input fw-semibold" data-field="title" placeholder="Alert title (optional)" value="' + escapeHtml(block.title) + '">'
                        + richTextEditHtml('content', block.content, block.id, 'Alert message...')
                        + '</div>';
                case 'image':
                    var imgPreview = block.url
                        ? '<figure class="cb-image cb-align-' + block.align + ' cb-w-' + block.width + ' mb-2">'
                            + '<img src="' + escapeHtml(block.url) + '" class="img-fluid cb-shape-' + block.shape + ' cb-aspect-' + block.aspect + '" alt="' + escapeHtml(block.caption) + '">'
                            + '</figure>'
                        : '<div class="cb-empty-media mb-2">No image yet — add a URL below</div>';
                    return imgPreview
                        + '<div class="content-block-inner">'
                        + '<div class="cb-settings-panel">'
                        + '<div class="input-group input-group-sm mb-2">'
                        + '<input type="url" class="form-control" data-field="url" placeholder="Image URL (https://...)" value="' + escapeHtml(block.url) + '">'
                        + '<button type="button" class="btn btn-outline-secondary" data-browse-library>' + iconHtml('bi-images', 'me-1') + 'Browse Library</button>'
                        + '</div>'
                        + '<div class="row g-2">'
                        + '<div class="col-6 col-md-3">' + selectHtml('width', IMAGE_WIDTHS, { full: 'Full Width', content: 'Content Width', small: 'Small', smaller: 'Smaller' }, block.width) + '</div>'
                        + '<div class="col-6 col-md-3">' + selectHtml('align', IMAGE_ALIGNS, { left: 'Left', center: 'Center', right: 'Right' }, block.align) + '</div>'
                        + '<div class="col-6 col-md-3">' + selectHtml('shape', IMAGE_SHAPES, { rounded: 'Rounded', square: 'Square', circle: 'Circle', 'as-is': 'As-is' }, block.shape) + '</div>'
                        + '<div class="col-6 col-md-3">' + selectHtml('aspect', IMAGE_ASPECTS, { natural: 'Natural', '16-9': '16:9', '4-3': '4:3', '1-1': '1:1' }, block.aspect) + '</div>'
                        + '</div></div>'
                        + '<input type="text" class="form-control form-control-sm mt-2" data-field="caption" placeholder="Caption (optional)" value="' + escapeHtml(block.caption) + '">'
                        + '</div>';
                case 'gallery':
                    var galleryImages = Array.isArray(block.images) ? block.images : [];
                    var galleryGrid = this.galleryGridHtml(block);
                    var galleryPreview = galleryGrid
                        ? '<div class="mb-2">' + galleryGrid + '</div>'
                        : '<div class="cb-empty-media mb-2">No images yet — add some below</div>';
                    var galleryRows = galleryImages.map(function (img) {
                        return '<div class="cb-gallery-edit-row d-flex gap-2 align-items-center mb-2" data-image-id="' + img.id + '">'
                            + '<input type="url" class="form-control form-control-sm" data-image-field="url" placeholder="Image URL (https://...)" value="' + escapeHtml(img.url) + '">'
                            + '<input type="text" class="form-control form-control-sm" data-image-field="caption" placeholder="Caption (optional)" value="' + escapeHtml(img.caption) + '">'
                            + '<button type="button" class="btn btn-sm btn-outline-danger flex-shrink-0" data-remove-image title="Remove image">' + iconHtml('bi-trash') + '</button>'
                            + '</div>';
                    }).join('');
                    return galleryPreview
                        + '<div class="content-block-inner">'
                        + '<div class="cb-settings-panel">'
                        + '<div class="row g-2 mb-2"><div class="col-6 col-md-3">'
                        + selectHtml('columns', GALLERY_COLUMNS.map(String), { '2': '2 Columns', '3': '3 Columns', '4': '4 Columns' }, String(block.columns))
                        + '</div></div>'
                        + '<div data-gallery-list>' + galleryRows + '</div>'
                        + '<div class="d-flex gap-2 mt-1">'
                        + '<button type="button" class="btn btn-sm btn-outline-primary" data-add-image>' + iconHtml('bi-plus-lg', 'me-1') + 'Add Image</button>'
                        + '<button type="button" class="btn btn-sm btn-outline-secondary" data-browse-library-gallery>' + iconHtml('bi-images', 'me-1') + 'Browse Library</button>'
                        + '</div>'
                        + '</div></div>';
                case 'iframe':
                    var ratioClass = block.aspect_ratio === '4:3' ? 'ratio-4x3' : 'ratio-16x9';
                    var embedPreview = block.url
                        ? '<div class="ratio ' + ratioClass + ' mb-2"><iframe src="' + escapeHtml(block.url) + '" allowfullscreen loading="lazy"></iframe></div>'
                        : '<div class="cb-empty-media mb-2">No video yet — add a YouTube or embed URL below</div>';
                    return embedPreview
                        + '<div class="content-block-inner">'
                        + '<div class="cb-settings-panel">'
                        + '<input type="url" class="form-control form-control-sm mb-2" data-field="url" placeholder="YouTube or embed URL (https://...)" value="' + escapeHtml(block.url) + '">'
                        + '<div class="row g-2">'
                        + '<div class="col-6 col-md-3">' + selectHtml('aspect_ratio', IFRAME_ASPECT_RATIOS, { '16:9': '16:9 Widescreen', '4:3': '4:3 Standard' }, block.aspect_ratio) + '</div>'
                        + '</div></div>'
                        + '<input type="text" class="form-control form-control-sm mt-2" data-field="caption" placeholder="Caption (optional)" value="' + escapeHtml(block.caption) + '">'
                        + '</div>';
                case 'raw_html':
                    return '<textarea class="form-control form-control-sm font-monospace mb-2" rows="6" data-field="content" placeholder="Paste or write raw HTML...">' + escapeHtml(block.content) + '</textarea>'
                        + '<div class="cb-raw-html-preview" data-raw-preview>' + (block.content || '<span class="text-muted fst-italic">Live preview will appear here.</span>') + '</div>';
                default:
                    return '';
            }
        },

        /**
         * The read-only grid markup shared by edit-mode's live preview and
         * previewHtml() — kept as one function so the two never drift.
         * Mirrors ContentBlockRenderer::renderGallery(), the authoritative
         * server-side copy.
         */
        galleryGridHtml: function (block) {
            var images = (Array.isArray(block.images) ? block.images : []).filter(function (img) { return img.url; });
            if (!images.length) {
                return '';
            }

            var items = images.map(function (img) {
                return '<div class="col"><figure class="mb-0">'
                    + '<img src="' + escapeHtml(img.url) + '" class="img-fluid rounded" alt="' + escapeHtml(img.caption) + '">'
                    + (img.caption ? '<figcaption class="text-muted small mt-1">' + escapeHtml(img.caption) + '</figcaption>' : '')
                    + '</figure></div>';
            }).join('');

            return '<div class="row ' + galleryRowClasses(block.columns) + ' g-3">' + items + '</div>';
        },

        alertVariantButtons: function (block) {
            var html = '<div class="cb-alert-variant-group btn-group btn-group-sm mb-2" role="group">';
            ALERT_VARIANTS.forEach(function (variant) {
                var active = block.variant === variant ? ' active' : '';
                html += '<button type="button" class="cb-alert-variant-btn btn btn-outline-secondary' + active + '" data-set-variant="' + variant + '">'
                    + iconHtml(ALERT_ICONS[variant], 'me-1') + variant.charAt(0).toUpperCase() + variant.slice(1) + '</button>';
            });
            html += '</div>';
            return html;
        },

        previewHtml: function (block) {
            switch (block.type) {
                case 'section':
                    return typeBadgeHtml('section')
                        + '<h2 class="cb-heading-2">' + escapeHtml(block.title) + '</h2>'
                        + (block.description ? '<div class="mt-2">' + block.description + '</div>' : '');
                case 'lecture':
                    return typeBadgeHtml('lecture')
                        + '<h3 class="cb-heading-3">' + escapeHtml(block.title) + '</h3>'
                        + (block.content ? '<div class="mt-2">' + block.content + '</div>' : '');
                case 'text':
                    return block.content ? '<div>' + block.content + '</div>' : '<p class="text-muted fst-italic mb-0">Empty text block.</p>';
                case 'alert':
                    return '<div class="cb-alert-box alert alert-' + block.variant + ' d-flex mb-0">'
                        + iconHtml(ALERT_ICONS[block.variant], 'flex-shrink-0 me-2 mt-1') + '<div>'
                        + (block.title ? '<div class="alert-heading fw-semibold mb-1">' + escapeHtml(block.title) + '</div>' : '')
                        + (block.content || '<span class="text-muted">Empty alert.</span>')
                        + '</div></div>';
                case 'image':
                    return block.url
                        ? '<figure class="cb-image cb-align-' + block.align + ' cb-w-' + block.width + ' mb-0">'
                            + '<img src="' + escapeHtml(block.url) + '" class="img-fluid cb-shape-' + block.shape + ' cb-aspect-' + block.aspect + '" alt="' + escapeHtml(block.caption) + '">'
                            + (block.caption ? '<figcaption class="text-muted small mt-1">' + escapeHtml(block.caption) + '</figcaption>' : '')
                            + '</figure>'
                        : '<div class="content-block-inner"><p class="text-muted fst-italic mb-0">No image URL set.</p></div>';
                case 'gallery':
                    return this.galleryGridHtml(block) || '<div class="content-block-inner"><p class="text-muted fst-italic mb-0">No images yet.</p></div>';
                case 'iframe':
                    var ratioClass = block.aspect_ratio === '4:3' ? 'ratio-4x3' : 'ratio-16x9';
                    return block.url
                        ? '<div class="ratio ' + ratioClass + '"><iframe src="' + escapeHtml(block.url) + '" allowfullscreen loading="lazy"></iframe></div>'
                            + (block.caption ? '<div class="text-muted small mt-1">' + escapeHtml(block.caption) + '</div>' : '')
                        : '<div class="content-block-inner"><p class="text-muted fst-italic mb-0">No video URL set.</p></div>';
                case 'raw_html':
                    return block.content || '<div class="content-block-inner"><p class="text-muted fst-italic mb-0">Empty raw HTML block.</p></div>';
                default:
                    return '';
            }
        },

        bindBlockEvents: function () {
            var self = this;

            this.container.querySelectorAll('.cb-block').forEach(function (wrapper) {
                var id = wrapper.dataset.id;

                wrapper.querySelector('[data-move="up"]').addEventListener('click', function () { self.moveBlock(id, 'up'); });
                wrapper.querySelector('[data-move="down"]').addEventListener('click', function () { self.moveBlock(id, 'down'); });
                wrapper.querySelector('[data-duplicate]').addEventListener('click', function () { self.duplicateBlock(id); });
                wrapper.querySelector('[data-remove]').addEventListener('click', function () { self.removeBlock(id); });

                wrapper.querySelectorAll('[data-set-variant]').forEach(function (btn) {
                    btn.addEventListener('click', function () { self.setVariant(id, btn.dataset.setVariant); });
                });

                // Opens the shared MediaPicker modal (assets/js/media-picker.js)
                // instead of requiring a URL to be typed/pasted by hand. The
                // manual URL input stays fully functional alongside this --
                // useful for external images not yet in the library.
                var browseImageBtn = wrapper.querySelector('[data-browse-library]');
                if (browseImageBtn && window.MediaPicker) {
                    browseImageBtn.addEventListener('click', function () {
                        window.MediaPicker.open({ multiple: false, csrfToken: self.csrfToken }).then(function (items) {
                            if (!items.length) {
                                return;
                            }
                            var block = self.blocks.find(function (b) { return b.id === id; });
                            if (!block) {
                                return;
                            }
                            block.url = items[0].url;
                            self.markDirty();
                            self.render();
                        });
                    });
                }

                var browseGalleryBtn = wrapper.querySelector('[data-browse-library-gallery]');
                if (browseGalleryBtn && window.MediaPicker) {
                    browseGalleryBtn.addEventListener('click', function () {
                        window.MediaPicker.open({ multiple: true, csrfToken: self.csrfToken }).then(function (items) {
                            if (!items.length) {
                                return;
                            }
                            var block = self.blocks.find(function (b) { return b.id === id; });
                            if (!block) {
                                return;
                            }
                            if (!Array.isArray(block.images)) {
                                block.images = [];
                            }
                            items.forEach(function (item) {
                                block.images.push({ id: generateId('img'), url: item.url, caption: '' });
                            });
                            self.markDirty();
                            self.render();
                        });
                    });
                }

                wrapper.addEventListener('click', function (e) {
                    if (e.target.closest('.cb-block-controls') || e.target.closest('[data-set-variant]')) {
                        return;
                    }
                    self.setActiveBlock(id);
                });

                wrapper.querySelectorAll('[data-field]').forEach(function (field) {
                    field.addEventListener('input', function () {
                        var block = self.blocks.find(function (b) { return b.id === id; });
                        if (!block) {
                            return;
                        }

                        var value = field.isContentEditable ? field.innerHTML : field.value;
                        block[field.dataset.field] = value;
                        self.markDirty();

                        // Raw HTML gets a live-patched preview without a full
                        // re-render, so the textarea keeps focus and cursor
                        // position while typing.
                        if (block.type === 'raw_html' && field.dataset.field === 'content') {
                            var preview = wrapper.querySelector('[data-raw-preview]');
                            if (preview) {
                                preview.innerHTML = value || '<span class="text-muted fst-italic">Live preview will appear here.</span>';
                            }
                        }

                        // The outline list is a separate DOM subtree from the
                        // field being typed into, so refreshing it here never
                        // disturbs focus/cursor — titles and captions show up
                        // in the sidebar as you type them.
                        self.renderOutline();
                    });

                    // Selects (layout/aspect choices) and URL fields refresh
                    // the live image/video preview once the value is
                    // committed (not on every keystroke), so typing a URL
                    // isn't interrupted mid-edit.
                    if (field.tagName === 'SELECT' || field.type === 'url') {
                        field.addEventListener('change', function () {
                            var block = self.blocks.find(function (b) { return b.id === id; });
                            if (block && block.type === 'iframe' && field.dataset.field === 'url') {
                                block.url = formatEmbedUrl(block.url);
                            }
                            self.render();
                        });
                    }
                });

                // Gallery's "images" array isn't a flat [data-field] on the
                // block, so it's managed directly against self.blocks rather
                // than through the generic field sync above (matching
                // exam-editor.js's add/remove-row pattern for its own
                // nested "options" array).
                var galleryAddImageBtn = wrapper.querySelector('[data-add-image]');
                if (galleryAddImageBtn) {
                    galleryAddImageBtn.addEventListener('click', function () {
                        var block = self.blocks.find(function (b) { return b.id === id; });
                        if (!block) {
                            return;
                        }
                        if (!Array.isArray(block.images)) {
                            block.images = [];
                        }
                        block.images.push({ id: generateId('img'), url: '', caption: '' });
                        self.markDirty();
                        self.render();
                    });
                }

                wrapper.querySelectorAll('[data-remove-image]').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        var block = self.blocks.find(function (b) { return b.id === id; });
                        if (!block) {
                            return;
                        }
                        var row = btn.closest('[data-image-id]');
                        var imageId = row ? row.dataset.imageId : null;
                        block.images = (block.images || []).filter(function (img) { return img.id !== imageId; });
                        self.markDirty();
                        self.render();
                    });
                });

                wrapper.querySelectorAll('[data-image-field]').forEach(function (field) {
                    field.addEventListener('input', function () {
                        var block = self.blocks.find(function (b) { return b.id === id; });
                        if (!block) {
                            return;
                        }
                        var row = field.closest('[data-image-id]');
                        var imageId = row ? row.dataset.imageId : null;
                        var image = (block.images || []).find(function (img) { return img.id === imageId; });
                        if (image) {
                            image[field.dataset.imageField] = field.value;
                            self.markDirty();
                        }
                    });

                    if (field.type === 'url') {
                        field.addEventListener('change', function () { self.render(); });
                    }
                });
            });
        },
    };

    window.CourseEditor = CourseEditor;
})();
