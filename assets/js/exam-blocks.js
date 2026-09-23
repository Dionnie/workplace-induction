(function () {
    'use strict';

    const container = document.getElementById('exam-questions');
    const hiddenInput = document.getElementById('exam_blocks_input');
    const addButton = document.querySelector('[data-add-question]');

    if (!container || !hiddenInput) {
        return;
    }

    function makeId(prefix) {
        return prefix + '_' + Math.random().toString(36).slice(2, 10);
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function addOption(optionsList, questionId, data) {
        data = data || {};
        const id = data.id || makeId('opt');
        const row = document.createElement('div');
        row.className = 'input-group input-group-sm mb-1 exam-option';
        row.dataset.id = id;
        row.innerHTML =
            '<div class="input-group-text">'
            + '<input type="radio" name="correct_' + questionId + '" data-field="correct" '
            + (data.correct ? 'checked' : '') + '>'
            + '</div>'
            + '<input type="text" class="form-control" data-field="text" placeholder="Option text" '
            + 'value="' + escapeHtml(data.text || '') + '">'
            + '<button type="button" class="btn btn-outline-danger" data-remove-option>&times;</button>';
        optionsList.appendChild(row);
    }

    function addQuestion(data) {
        data = data || {};
        const id = data.id || makeId('blk_q');
        const hint = document.getElementById('no-questions-hint');
        if (hint) {
            hint.remove();
        }

        const card = document.createElement('div');
        card.className = 'card mb-3 exam-question';
        card.dataset.id = id;
        card.innerHTML =
            '<div class="card-body">'
            + '<div class="d-flex justify-content-between align-items-center mb-2">'
            + '<span class="badge text-bg-secondary">Question</span>'
            + '<button type="button" class="btn btn-sm btn-outline-danger" data-remove-question>Remove</button>'
            + '</div>'
            + '<textarea class="form-control mb-2" data-field="question" rows="2" '
            + 'placeholder="Question text">' + escapeHtml(data.question || '') + '</textarea>'
            + '<input type="url" class="form-control mb-2" data-field="diagram_img_url" '
            + 'placeholder="Diagram image URL (optional)" value="' + escapeHtml(data.diagram_img_url || '') + '">'
            + '<textarea class="form-control mb-2" data-field="explanation" rows="2" '
            + 'placeholder="Explanation shown after answering (optional)">' + escapeHtml(data.explanation || '') + '</textarea>'
            + '<label class="form-label small text-muted mb-1">Options (select the correct one)</label>'
            + '<div class="options-list"></div>'
            + '<button type="button" class="btn btn-sm btn-outline-primary mt-1" data-add-option>+ Add Option</button>'
            + '</div>';

        container.appendChild(card);

        const optionsList = card.querySelector('.options-list');
        const options = Array.isArray(data.options) ? data.options : [];
        if (options.length > 0) {
            options.forEach(function (option) {
                addOption(optionsList, id, option);
            });
        } else {
            addOption(optionsList, id, {});
            addOption(optionsList, id, {});
        }
    }

    if (addButton) {
        addButton.addEventListener('click', function () {
            addQuestion({});
        });
    }

    container.addEventListener('click', function (event) {
        const removeQuestion = event.target.closest('[data-remove-question]');
        if (removeQuestion) {
            removeQuestion.closest('.exam-question').remove();
            return;
        }

        const addOptionBtn = event.target.closest('[data-add-option]');
        if (addOptionBtn) {
            const question = addOptionBtn.closest('.exam-question');
            addOption(question.querySelector('.options-list'), question.dataset.id, {});
            return;
        }

        const removeOption = event.target.closest('[data-remove-option]');
        if (removeOption) {
            const optionsList = removeOption.closest('.options-list');
            if (optionsList.querySelectorAll('.exam-option').length > 2) {
                removeOption.closest('.exam-option').remove();
            }
        }
    });

    function serialize() {
        const questions = [];
        container.querySelectorAll('.exam-question').forEach(function (card) {
            const question = {
                id: card.dataset.id,
                type: 'question',
                question: card.querySelector('[data-field="question"]').value,
                diagram_img_url: card.querySelector('[data-field="diagram_img_url"]').value,
                explanation: card.querySelector('[data-field="explanation"]').value,
                options: [],
            };

            card.querySelectorAll('.exam-option').forEach(function (row) {
                question.options.push({
                    id: row.dataset.id,
                    text: row.querySelector('[data-field="text"]').value,
                    correct: row.querySelector('[data-field="correct"]').checked,
                });
            });

            questions.push(question);
        });
        return questions;
    }

    const form = container.closest('form');
    form.addEventListener('submit', function () {
        hiddenInput.value = JSON.stringify(serialize());
    });

    const initial = container.dataset.initial;
    if (initial) {
        try {
            JSON.parse(initial).forEach(function (question) {
                addQuestion(question);
            });
        } catch (e) {
            // Ignore malformed initial data; the builder just starts empty.
        }
    }
})();
