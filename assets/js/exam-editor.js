/**
 * Exam Blocks Studio editor (views/admin/exams/editor.php). See
 * docs/application/exams.md.
 *
 * The same editing model as the Content Blocks Studio (course-editor.js):
 * only the selected block shows its edit fields; every other block previews
 * as inductees see it on the exam page (views/inductee/exams/take.php): the
 * same question card (app.css §12), with the correct answer marked for the
 * admin. Kept as a parallel file rather than a shared editor, per this
 * project's convention of small, single-purpose JS files.
 *
 * Exam blocks are questions only (docs/application/exams.md §2). The
 * checks in problemsFor() mirror ExamService::parseExamBlocks(), which is
 * the authoritative copy; here they only drive warnings while editing.
 */
(function () {
    'use strict';

    // Both add a "question" block; they only differ in the starting options.
    var QUESTION_TEMPLATES = {
        multiple_choice: { label: 'Multiple Choice', icon: 'bi-ui-radios', options: ['', '', '', ''] },
        true_false: { label: 'True / False', icon: 'bi-toggle-on', options: ['True', 'False'] },
    };

    function escapeHtml(value) {
        var div = document.createElement('div');
        div.textContent = value == null ? '' : String(value);
        return div.innerHTML;
    }

    function nl2br(escaped) {
        return escaped.replace(/\n/g, '<br>');
    }

    function iconHtml(name, extraClass) {
        return '<i class="bi ' + name + (extraClass ? ' ' + extraClass : '') + '" aria-hidden="true"></i>';
    }

    function generateId(prefix) {
        return prefix + '_' + Math.random().toString(36).slice(2, 10);
    }

    function trim(value) {
        return value == null ? '' : String(value).trim();
    }

    /**
     * What stops this question from saving, in the same order and wording
     * as ExamService::parseExamBlocks().
     */
    function problemsFor(block) {
        var problems = [];
        var options = Array.isArray(block.options) ? block.options : [];

        if (trim(block.question) === '') {
            problems.push('Enter the question.');
        }

        var blank = options.findIndex(function (option) { return trim(option.text) === ''; });
        if (blank !== -1) {
            problems.push('Option ' + (blank + 1) + ' is empty. Fill it in or remove it.');
        }

        if (options.length < 2) {
            problems.push('Add at least two options.');
        }

        var correct = options.filter(function (option) { return option.correct; }).length;
        if (correct !== 1) {
            problems.push('Mark the one correct answer.');
        }

        return problems;
    }

    var ExamEditor = {
        init: function (options) {
            this.container = document.querySelector(options.blocksContainer);
            this.emptyHint = document.querySelector(options.emptyHint);

            // The outline renders into both the floating rail (wide
            // viewports) and the offcanvas drawer (everywhere else).
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
                            block.id = block.id || generateId('blk_q');
                            block.type = 'question';
                            block.options = (Array.isArray(block.options) ? block.options : []).map(function (option) {
                                option.id = option.id || generateId('opt');
                                option.correct = !!option.correct;
                                return option;
                            });
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

        newQuestion: function (template) {
            var preset = QUESTION_TEMPLATES[template] || QUESTION_TEMPLATES.multiple_choice;
            return {
                id: generateId('blk_q'),
                type: 'question',
                question: '',
                diagram_img_url: '',
                explanation: '',
                options: preset.options.map(function (text) {
                    return { id: generateId('opt'), text: text, correct: false };
                }),
            };
        },

        find: function (id) {
            return this.blocks.find(function (b) { return b.id === id; });
        },

        addBlock: function (template, afterId) {
            this.syncActiveBlockData();

            var block = this.newQuestion(template);
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
            if (!window.confirm('Remove this question?')) {
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
            var swapWith = direction === 'up' ? index - 1 : index + 1;
            if (index === -1 || swapWith < 0 || swapWith >= this.blocks.length) {
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

            // New ids for the copy and its options: the inductee's answers
            // are keyed by these ids, so they must stay unique.
            var clone = JSON.parse(JSON.stringify(this.blocks[index]));
            clone.id = generateId('blk_q');
            clone.options.forEach(function (option) { option.id = generateId('opt'); });
            this.blocks.splice(index + 1, 0, clone);

            this.activeBlockId = null;
            this.focusedBlockId = clone.id;
            this.markDirty();
            this.render();
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
                var field = self.container.querySelector('.cb-block.active [data-field="question"]');
                if (field) {
                    field.focus();
                }
            }, 20);
        },

        // Question, diagram and explanation fields. Options are kept in sync
        // by their own handlers in bindBlockEvents().
        syncActiveBlockData: function () {
            if (!this.activeBlockId) {
                return;
            }

            var wrapper = this.container.querySelector('.cb-block[data-id="' + this.activeBlockId + '"]');
            var block = this.find(this.activeBlockId);
            if (!wrapper || !block) {
                return;
            }

            wrapper.querySelectorAll('[data-field]').forEach(function (field) {
                block[field.dataset.field] = field.value;
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
            body.set('exam_blocks', JSON.stringify(this.blocks));

            var self = this;
            fetch(this.saveUrl, { method: 'POST', body: body })
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    if (data.success) {
                        self.markSaved();
                        return;
                    }

                    window.alert('Not saved. ' + data.message);

                    // Take the admin straight to the question that needs fixing.
                    if (data.block_id && self.find(data.block_id)) {
                        self.setActiveBlock(data.block_id);
                        var target = document.getElementById('block-' + data.block_id);
                        if (target) {
                            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
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

            // Leaving with unsaved questions asks first.
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

            // Outline teleport scroll, the same as the Content Blocks Studio.
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

                if (self.offcanvasEl && self.offcanvasEl.contains(link) && window.bootstrap) {
                    var instance = window.bootstrap.Offcanvas.getInstance(self.offcanvasEl);
                    if (instance) {
                        instance.hide();
                    }
                }

                target.scrollIntoView({ behavior: 'smooth', block: 'center' });

                // The question card covers its block, so the card pulses.
                var card = target.querySelector('.exam-question') || target;
                card.classList.remove('teleport-highlight-pulse');
                void card.offsetWidth;
                card.classList.add('teleport-highlight-pulse');
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
         * One numbered entry per question, flagged when it isn't ready to
         * save yet.
         */
        renderOutline: function () {
            if (this.outlineLists.length === 0) {
                return;
            }

            var html = this.blocks.map(function (block, index) {
                var label = trim(block.question) || 'Untitled question';
                var incomplete = problemsFor(block).length > 0;
                return '<li><a href="#block-' + block.id + '" class="cb-outline-link small text-truncate" data-teleport="block-' + block.id + '" title="' + escapeHtml(label) + '">'
                    + '<span class="text-muted me-1">' + (index + 1) + '.</span>'
                    + (incomplete ? iconHtml('bi-exclamation-circle', 'text-warning-emphasis me-1') + '<span class="visually-hidden">Incomplete: </span>' : '')
                    + escapeHtml(label) + '</a></li>';
            }).join('');

            this.outlineLists.forEach(function (list) { list.innerHTML = html; });
            this.outlineEmpties.forEach(function (empty) {
                empty.style.display = this.blocks.length === 0 ? '' : 'none';
            }, this);
        },

        insertQuickInsertBar: function (afterEl) {
            var bar = document.createElement('div');
            bar.className = 'cb-quick-insert d-flex flex-wrap gap-2 justify-content-center py-3 my-3 border border-dashed rounded';

            var self = this;
            bar.innerHTML = Object.keys(QUESTION_TEMPLATES).map(function (key) {
                var template = QUESTION_TEMPLATES[key];
                return '<button type="button" class="cb-add-block-btn btn btn-outline-secondary" data-quick-add="' + key + '">'
                    + iconHtml(template.icon, 'me-1') + template.label + '</button>';
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
            wrapper.dataset.type = 'question';

            var controls = '<div class="cb-block-controls">'
                + '<button type="button" class="cb-block-control-btn btn" data-move="up" title="Move up" aria-label="Move question up" ' + (index === 0 ? 'disabled' : '') + '>' + iconHtml('bi-arrow-up') + '</button>'
                + '<button type="button" class="cb-block-control-btn btn" data-move="down" title="Move down" aria-label="Move question down" ' + (index === this.blocks.length - 1 ? 'disabled' : '') + '>' + iconHtml('bi-arrow-down') + '</button>'
                + '<button type="button" class="cb-block-control-btn btn" data-duplicate title="Duplicate" aria-label="Duplicate question">' + iconHtml('bi-copy') + '</button>'
                + '<button type="button" class="cb-block-control-btn btn" data-remove title="Remove" aria-label="Remove question">' + iconHtml('bi-trash') + '</button>'
                + '</div>';

            var number = index + 1;
            var body = isActive ? this.editHtml(block, number) : this.previewHtml(block, number);

            wrapper.innerHTML = controls
                + '<div class="exam-question card border-0 shadow-sm"><div class="card-body">' + body + '</div></div>';

            return wrapper;
        },

        // "Question 3 of 20", as on the exam page.
        eyebrowHtml: function (number) {
            return '<p class="exam-question-eyebrow">Question ' + number + ' of ' + this.blocks.length + '</p>';
        },

        diagramHtml: function (block) {
            return block.diagram_img_url
                ? '<img src="' + escapeHtml(block.diagram_img_url) + '" class="exam-question-diagram img-fluid rounded" alt="">'
                : '';
        },

        // The question card with its fields in place: the question text and
        // each option are edited where the inductee reads them.
        editHtml: function (block, number) {
            var canRemoveOption = block.options.length > 2;
            var options = block.options.map(function (option, i) {
                return '<div class="exam-choice exam-choice-edit' + (option.correct ? ' is-correct' : '') + '" data-option-id="' + option.id + '">'
                    + '<input class="form-check-input exam-choice-radio" type="radio" name="correct_' + block.id + '" data-option-correct '
                    + 'title="Mark as the correct answer" aria-label="Option ' + (i + 1) + ' is the correct answer"' + (option.correct ? ' checked' : '') + '>'
                    + '<input type="text" class="exam-choice-input" data-option-text '
                    + 'placeholder="Option ' + (i + 1) + '" aria-label="Option ' + (i + 1) + '" value="' + escapeHtml(option.text) + '">'
                    + (option.correct ? '<span class="exam-choice-note">Correct</span>' : '')
                    + '<button type="button" class="exam-choice-remove btn btn-sm" data-remove-option title="Remove option" '
                    + 'aria-label="Remove option ' + (i + 1) + '"' + (canRemoveOption ? '' : ' disabled') + '>' + iconHtml('bi-x-lg') + '</button>'
                    + '</div>';
            }).join('');

            return this.eyebrowHtml(number)
                + '<textarea class="cb-title-input cb-question-input exam-question-text" data-field="question" rows="2" placeholder="Write the question..." '
                + 'aria-label="Question ' + number + '">' + escapeHtml(block.question) + '</textarea>'
                + this.diagramHtml(block)
                + '<div class="form-label small text-muted mb-2 required">Options (select the correct answer)</div>'
                + '<div class="exam-choices">' + options + '</div>'
                + '<button type="button" class="btn btn-sm btn-outline-primary mt-2" data-add-option>' + iconHtml('bi-plus-lg', 'me-1') + 'Add Option</button>'
                + '<div class="cb-settings-panel mt-3">'
                + '<div class="input-group input-group-sm">'
                + '<input type="url" class="form-control" data-field="diagram_img_url" placeholder="Diagram image URL (optional)" '
                + 'aria-label="Diagram image URL" value="' + escapeHtml(block.diagram_img_url) + '">'
                + '<button type="button" class="btn btn-outline-secondary" data-browse-library>' + iconHtml('bi-images', 'me-1') + 'Browse Library</button>'
                + '</div></div>'
                + '<div class="cb-settings-panel">'
                + '<textarea class="form-control form-control-sm" data-field="explanation" rows="2" '
                + 'placeholder="Explanation shown with the exam result (optional)" aria-label="Explanation">' + escapeHtml(block.explanation) + '</textarea>'
                + '</div>';
        },

        /**
         * The question card as inductees see it on the exam page, plus what
         * only the admin needs: the correct answer (marked as on the result
         * page), the explanation, and what is still missing.
         */
        previewHtml: function (block, number) {
            var options = block.options.map(function (option) {
                var text = trim(option.text) ? escapeHtml(option.text) : '<span class="text-muted fst-italic">Empty option</span>';
                return '<div class="exam-choice' + (option.correct ? ' is-correct' : '') + '">'
                    + iconHtml(option.correct ? 'bi-check-circle-fill' : 'bi-circle', 'exam-choice-icon')
                    + '<span class="exam-choice-text">' + text + '</span>'
                    + (option.correct ? '<span class="exam-choice-note">Correct</span>' : '')
                    + '</div>';
            }).join('');

            var problems = problemsFor(block);

            return this.eyebrowHtml(number)
                + '<p class="exam-question-text">' + (trim(block.question) ? nl2br(escapeHtml(block.question)) : '<span class="text-muted fst-italic fw-normal">No question yet.</span>') + '</p>'
                + this.diagramHtml(block)
                + '<div class="exam-choices">' + options + '</div>'
                + (trim(block.explanation)
                    ? '<p class="exam-explanation">' + iconHtml('bi-lightbulb', 'me-1') + nl2br(escapeHtml(block.explanation)) + '</p>'
                    : '')
                + (problems.length
                    ? '<p class="text-warning-emphasis small mt-3 mb-0">' + iconHtml('bi-exclamation-triangle', 'me-1') + problems.join(' ') + '</p>'
                    : '');
        },

        bindBlockEvents: function () {
            var self = this;

            this.container.querySelectorAll('.cb-block').forEach(function (wrapper) {
                var id = wrapper.dataset.id;
                var block = self.find(id);

                wrapper.querySelector('[data-move="up"]').addEventListener('click', function () { self.moveBlock(id, 'up'); });
                wrapper.querySelector('[data-move="down"]').addEventListener('click', function () { self.moveBlock(id, 'down'); });
                wrapper.querySelector('[data-duplicate]').addEventListener('click', function () { self.duplicateBlock(id); });
                wrapper.querySelector('[data-remove]').addEventListener('click', function () { self.removeBlock(id); });

                wrapper.addEventListener('click', function (e) {
                    if (e.target.closest('.cb-block-controls')) {
                        return;
                    }
                    self.setActiveBlock(id);
                });

                wrapper.querySelectorAll('[data-field]').forEach(function (field) {
                    field.addEventListener('input', function () {
                        block[field.dataset.field] = field.value;
                        self.markDirty();
                        self.renderOutline();
                    });

                    // The diagram preview refreshes once the URL is committed,
                    // not on every keystroke.
                    if (field.type === 'url') {
                        field.addEventListener('change', function () { self.render(); });
                    }
                });

                // Opens the shared MediaPicker (assets/js/media-picker.js); the
                // URL field still accepts any image address typed by hand.
                var browseBtn = wrapper.querySelector('[data-browse-library]');
                if (browseBtn && window.MediaPicker) {
                    browseBtn.addEventListener('click', function () {
                        window.MediaPicker.open({ multiple: false, title: 'Select a Diagram', csrfToken: self.csrfToken }).then(function (items) {
                            if (items.length) {
                                block.diagram_img_url = items[0].url;
                                self.markDirty();
                                self.render();
                            }
                        });
                    });
                }

                // Options are a nested array, managed directly on the block
                // (like the Content Blocks gallery's images).
                wrapper.querySelectorAll('[data-option-id]').forEach(function (row) {
                    var option = block.options.find(function (o) { return o.id === row.dataset.optionId; });

                    row.querySelector('[data-option-text]').addEventListener('input', function (e) {
                        option.text = e.target.value;
                        self.markDirty();
                        self.renderOutline();
                    });

                    row.querySelector('[data-option-correct]').addEventListener('change', function () {
                        self.syncActiveBlockData();
                        block.options.forEach(function (o) { o.correct = o.id === option.id; });
                        self.markDirty();
                        self.render();
                    });

                    row.querySelector('[data-remove-option]').addEventListener('click', function () {
                        self.syncActiveBlockData();
                        block.options = block.options.filter(function (o) { return o.id !== option.id; });
                        self.markDirty();
                        self.render();
                    });
                });

                var addOptionBtn = wrapper.querySelector('[data-add-option]');
                if (addOptionBtn) {
                    addOptionBtn.addEventListener('click', function () {
                        self.syncActiveBlockData();
                        block.options.push({ id: generateId('opt'), text: '', correct: false });
                        self.markDirty();
                        self.render();

                        var inputs = self.container.querySelectorAll('.cb-block.active [data-option-text]');
                        if (inputs.length) {
                            inputs[inputs.length - 1].focus();
                        }
                    });
                }
            });
        },
    };

    window.ExamEditor = ExamEditor;
})();
