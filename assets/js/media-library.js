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

    // Every category row starts here: name + rename/delete icons, on one
    // line. Renaming swaps the whole line for an input below it; deleting
    // just swaps the rename/delete icons for confirm/cancel icons in place,
    // so the row never grows or wraps.
    function reset(li) {
        li.querySelector('.category-view').classList.remove('d-none');
        li.querySelector('.category-actions').classList.remove('d-none');

        var deleteConfirm = li.querySelector('.category-delete-confirm');
        deleteConfirm.classList.remove('d-flex');
        deleteConfirm.classList.add('d-none');

        var editForm = li.querySelector('.category-edit-form');
        editForm.classList.remove('d-flex');
        editForm.classList.add('d-none');
    }

    document.querySelectorAll('.category-rename-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var li = btn.closest('li');
            reset(li);
            li.querySelector('.category-view').classList.add('d-none');
            var form = li.querySelector('.category-edit-form');
            form.classList.remove('d-none');
            form.classList.add('d-flex');
            var input = form.querySelector('input[name="name"]');
            input.focus();
            input.select();
        });
    });

    document.querySelectorAll('.category-delete-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var li = btn.closest('li');
            reset(li);
            li.querySelector('.category-actions').classList.add('d-none');
            var confirmForm = li.querySelector('.category-delete-confirm');
            confirmForm.classList.remove('d-none');
            confirmForm.classList.add('d-flex');
        });
    });

    document.querySelectorAll('.category-cancel-btn, .category-delete-cancel-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            reset(btn.closest('li'));
        });
    });

    document.querySelectorAll('.category-edit-form, .category-delete-confirm').forEach(function (panel) {
        panel.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                reset(panel.closest('li'));
            }
        });
    });
})();
