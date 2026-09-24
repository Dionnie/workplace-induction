(function () {
    'use strict';

    var dropzone = document.getElementById('media-dropzone');
    var fileInput = document.getElementById('media-file-input');
    var categorySelect = document.getElementById('media-category-select');
    var alerts = document.getElementById('media-upload-alerts');

    if (!dropzone || !fileInput || !categorySelect) {
        return;
    }

    function setDragging(isDragging) {
        dropzone.classList.toggle('border-primary', isDragging);
    }

    function showAlert(type, message) {
        var div = document.createElement('div');
        div.className = 'alert alert-' + type + ' py-2 px-3 small mb-1';
        div.textContent = message;
        alerts.appendChild(div);
    }

    function uploadFile(file) {
        var body = new FormData();
        body.append('file', file);
        body.append('category_id', categorySelect.value);
        body.append('csrf_token', window.MEDIA_LIBRARY_CSRF_TOKEN);

        return fetch(window.MEDIA_LIBRARY_UPLOAD_URL, { method: 'POST', body: body })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (!data.success) {
                    var message = data.errors && (data.errors.file || data.errors.category_id);
                    showAlert('danger', message || ('Failed to upload "' + file.name + '".'));
                }
                return data.success;
            })
            .catch(function () {
                showAlert('danger', 'Network error while uploading "' + file.name + '".');
                return false;
            });
    }

    function uploadFiles(fileList) {
        var files = Array.prototype.slice.call(fileList);
        if (files.length === 0) {
            return;
        }

        alerts.innerHTML = '';
        dropzone.setAttribute('aria-busy', 'true');

        var uploadedAny = false;

        files.reduce(function (chain, file) {
            return chain.then(function () {
                return uploadFile(file).then(function (success) {
                    uploadedAny = uploadedAny || success;
                });
            });
        }, Promise.resolve()).then(function () {
            dropzone.removeAttribute('aria-busy');
            fileInput.value = '';
            if (uploadedAny) {
                window.location.reload();
            }
        });
    }

    dropzone.addEventListener('click', function () {
        fileInput.click();
    });

    dropzone.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            fileInput.click();
        }
    });

    fileInput.addEventListener('change', function () {
        uploadFiles(fileInput.files);
    });

    ['dragenter', 'dragover'].forEach(function (eventName) {
        dropzone.addEventListener(eventName, function (event) {
            event.preventDefault();
            event.stopPropagation();
            setDragging(true);
        });
    });

    ['dragleave', 'drop'].forEach(function (eventName) {
        dropzone.addEventListener(eventName, function (event) {
            event.preventDefault();
            event.stopPropagation();
            setDragging(false);
        });
    });

    dropzone.addEventListener('drop', function (event) {
        var files = event.dataTransfer ? event.dataTransfer.files : null;
        if (files && files.length > 0) {
            uploadFiles(files);
        }
    });
})();

(function () {
    'use strict';

    var selectAll = document.getElementById('media-select-all');
    var countLabel = document.getElementById('media-selected-count');
    var moveButton = document.getElementById('media-bulk-move-btn');
    var deleteButton = document.getElementById('media-bulk-delete-btn');

    if (!selectAll || !countLabel || !moveButton || !deleteButton) {
        return;
    }

    function checkboxes() {
        return Array.prototype.slice.call(document.querySelectorAll('.media-select'));
    }

    function refresh() {
        var boxes = checkboxes();
        var checked = boxes.filter(function (box) { return box.checked; });

        countLabel.textContent = checked.length + ' selected';
        moveButton.disabled = checked.length === 0;
        deleteButton.disabled = checked.length === 0;
        selectAll.checked = boxes.length > 0 && checked.length === boxes.length;
    }

    selectAll.addEventListener('change', function () {
        checkboxes().forEach(function (box) {
            box.checked = selectAll.checked;
        });
        refresh();
    });

    document.addEventListener('change', function (event) {
        if (event.target.classList && event.target.classList.contains('media-select')) {
            refresh();
        }
    });

    refresh();
})();

(function () {
    'use strict';

    // Every category row starts as its name + an Edit icon. Edit swaps the
    // row for the rename input; only from there can the category be deleted
    // (docs/core/design-system.html#danger-zone: no delete buttons in lists),
    // and "Delete category" still asks to confirm.
    function showDeleteConfirm(li, show) {
        var confirmForm = li.querySelector('.category-delete-confirm');
        confirmForm.classList.toggle('d-none', !show);
        confirmForm.classList.toggle('d-flex', show);
        li.querySelector('.category-delete-btn').classList.toggle('d-none', show);
    }

    function reset(li) {
        li.querySelector('.category-view').classList.remove('d-none');
        li.querySelector('.category-edit').classList.add('d-none');
        showDeleteConfirm(li, false);
    }

    document.querySelectorAll('.category-rename-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var li = btn.closest('li');
            reset(li);
            li.querySelector('.category-view').classList.add('d-none');
            li.querySelector('.category-edit').classList.remove('d-none');
            var input = li.querySelector('.category-edit input[name="name"]');
            input.focus();
            input.select();
        });
    });

    document.querySelectorAll('.category-delete-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            showDeleteConfirm(btn.closest('li'), true);
        });
    });

    document.querySelectorAll('.category-delete-cancel-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            showDeleteConfirm(btn.closest('li'), false);
        });
    });

    document.querySelectorAll('.category-cancel-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            reset(btn.closest('li'));
        });
    });

    document.querySelectorAll('.category-edit').forEach(function (panel) {
        panel.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                reset(panel.closest('li'));
            }
        });
    });
})();
