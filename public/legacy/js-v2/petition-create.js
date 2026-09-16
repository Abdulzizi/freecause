document.addEventListener('DOMContentLoaded', function () {
    var editorEl = document.getElementById('petition_editor');
    var hidden = document.getElementById('petition_description');
    if (!editorEl || !hidden || typeof Quill === 'undefined') return;

    var quill = new Quill(editorEl, {
        theme: 'snow',
        modules: {
            toolbar: '#fc-quill-toolbar',
            clipboard: { matchVisual: false }
        }
    });

    var initialHtml = (hidden.value || '').trim();
    if (initialHtml) {
        quill.clipboard.dangerouslyPasteHTML(initialHtml);
    }

    function syncHidden() {
        hidden.value = quill.root.innerHTML;
    }

    quill.on('text-change', syncHidden);
    syncHidden();

    var form = editorEl.closest('form');
    if (form) form.addEventListener('submit', syncHidden);
});

document.addEventListener('DOMContentLoaded', function () {
    var form = document.querySelector('[data-petition-form]');
    if (!form) return;

    var fileInput = form.querySelector('[data-image-input]');
    var urlInput = form.querySelector('[data-image-url]');
    var preview = form.querySelector('[data-image-preview]');
    var previewImg = form.querySelector('[data-image-preview-img]');
    var nameEl = form.querySelector('[data-image-name]');
    var sizeEl = form.querySelector('[data-image-size]');
    var removeBtn = form.querySelector('[data-image-remove]');
    var submitBtn = form.querySelector('[data-submit-btn]');

    var MAX_SIZE = fileInput ? parseInt(fileInput.getAttribute('data-max-size'), 10) || 4194304 : 4194304;
    var ACCEPTED = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    var objectUrl = null;

    function formatSize(bytes) {
        if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
        if (bytes >= 1024) return (bytes / 1024).toFixed(0) + ' KB';
        return bytes + ' B';
    }

    function setError(input, message) {
        if (!input) return;
        input.classList.add('is-invalid');
        var fb = input.parentElement.querySelector('.invalid-feedback');
        if (!fb) {
            fb = document.createElement('div');
            fb.className = 'invalid-feedback d-block';
            input.parentElement.appendChild(fb);
        }
        fb.classList.add('d-block');
        fb.textContent = message;
    }

    function clearError(input) {
        if (!input) return;
        input.classList.remove('is-invalid');
        var fb = input.parentElement.querySelector('.invalid-feedback');
        if (fb) fb.textContent = '';
    }

    function resetPreview() {
        if (objectUrl) {
            URL.revokeObjectURL(objectUrl);
            objectUrl = null;
        }
        if (preview) preview.classList.add('d-none');
        if (previewImg) previewImg.removeAttribute('src');
        if (nameEl) nameEl.textContent = '';
        if (sizeEl) sizeEl.textContent = '';
    }

    function validateFile(file) {
        clearError(fileInput);
        if (!file) return true;

        var typeOk = ACCEPTED.indexOf(file.type) !== -1 ||
            /\.(jpe?g|png|gif|webp)$/i.test(file.name);

        if (!typeOk) {
            setError(fileInput, 'Unsupported file type. Use JPG, PNG, GIF, or WebP.');
            return false;
        }
        if (file.size > MAX_SIZE) {
            setError(fileInput, 'The image may not be larger than 4 MB.');
            return false;
        }
        return true;
    }

    if (fileInput) {
        fileInput.addEventListener('change', function () {
            resetPreview();
            var file = fileInput.files && fileInput.files[0];
            if (!file) {
                syncMutualExclusion();
                return;
            }

            if (!validateFile(file)) {
                fileInput.value = '';
                syncMutualExclusion();
                return;
            }

            if (preview && previewImg) {
                objectUrl = URL.createObjectURL(file);
                previewImg.src = objectUrl;
                if (nameEl) nameEl.textContent = file.name;
                if (sizeEl) sizeEl.textContent = '(' + formatSize(file.size) + ')';
                preview.classList.remove('d-none');
            }

            syncMutualExclusion();
        });
    }

    if (removeBtn) {
        removeBtn.addEventListener('click', function () {
            if (fileInput) fileInput.value = '';
            clearError(fileInput);
            resetPreview();
            syncMutualExclusion();
        });
    }

    if (urlInput) {
        urlInput.addEventListener('input', syncMutualExclusion);
    }

    function syncMutualExclusion() {
        var hasFile = !!(fileInput && fileInput.files && fileInput.files.length > 0);
        var hasUrl = !!(urlInput && urlInput.value.trim() !== '');

        if (fileInput) {
            fileInput.disabled = hasUrl;
            fileInput.classList.toggle('disabled', hasUrl);
        }
        if (urlInput) {
            urlInput.disabled = hasFile;
            urlInput.classList.toggle('disabled', hasFile);
        }
    }

    form.addEventListener('submit', function (e) {
        var file = fileInput && fileInput.files && fileInput.files[0];

        if (file && urlInput && urlInput.value.trim() !== '') {
            e.preventDefault();
            setError(fileInput, 'Choose either an uploaded image OR an external link, not both.');
            setError(urlInput, 'Choose either an uploaded image OR an external link, not both.');
            focusFirstError();
            return;
        }

        if (file && !validateFile(file)) {
            e.preventDefault();
            focusFirstError();
            return;
        }

        if (submitBtn) {
            var busy = submitBtn.getAttribute('data-submitting-text');
            submitBtn.disabled = true;
            if (busy) submitBtn.textContent = busy;
        }
    });

    function focusFirstError() {
        var firstInvalid = form.querySelector('.is-invalid');
        if (firstInvalid) {
            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            if (typeof firstInvalid.focus === 'function') firstInvalid.focus({ preventScroll: true });
        }
    }

    focusFirstError();
    syncMutualExclusion();
});
