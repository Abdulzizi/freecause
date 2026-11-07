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

    quill.on('text-change', function () {
        syncHidden();
        hidden.dispatchEvent(new Event('input', { bubbles: true }));
    });
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
    var submitBtn = null;

    var MAX_SIZE = fileInput ? parseInt(fileInput.getAttribute('data-max-size'), 10) || 8388608 : 8388608;
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
            fb.className = 'invalid-feedback block';
            input.parentElement.appendChild(fb);
        }
        fb.classList.remove('hidden');
        fb.classList.add('block');
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
        if (preview) preview.classList.add('hidden');
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
            setError(fileInput, form.dataset.msgType || 'Unsupported file type.');
            return false;
        }
        if (file.size > MAX_SIZE) {
            setError(fileInput, form.dataset.msgSize || 'File too large.');
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
                preview.classList.remove('hidden');
            }

            syncMutualExclusion();
        });
    }

    if (removeBtn) {
        removeBtn.addEventListener('click', function () {
            if (fileInput) fileInput.value = '';
            clearError(fileInput);
            if (urlInput) {
                urlInput.value = '';
                clearError(urlInput);
            }
            resetPreview();
            syncMutualExclusion();
        });
    }

    var urlTimer = null;

    function showUrlPreview() {
        var hasFile = !!(fileInput && fileInput.files && fileInput.files.length > 0);
        if (hasFile) return;
        var url = urlInput ? urlInput.value.trim() : '';
        if (!/^https?:\/\/.+\..+/.test(url)) {
            resetPreview();
            return;
        }
        if (preview && previewImg) {
            previewImg.src = url;
            if (nameEl) nameEl.textContent = url;
            if (sizeEl) sizeEl.textContent = '';
            preview.classList.remove('hidden');
        }
    }

    if (previewImg) {
        previewImg.addEventListener('error', function () {
            var hasFile = !!(fileInput && fileInput.files && fileInput.files.length > 0);
            if (!hasFile) resetPreview();
        });
    }

    if (urlInput) {
        urlInput.addEventListener('input', function () {
            clearError(urlInput);
            syncMutualExclusion();
            if (urlTimer) clearTimeout(urlTimer);
            urlTimer = setTimeout(showUrlPreview, 400);
        });
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
            var msg = form.dataset.msgConflict || 'Choose one option.';
            setError(fileInput, msg);
            setError(urlInput, msg);
            focusFirstError();
            return;
        }

        if (file && !validateFile(file)) {
            e.preventDefault();
            focusFirstError();
            return;
        }

        if (submitBtn) {
            submitBtn.disabled = true;
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

document.addEventListener('DOMContentLoaded', function () {
    var form = document.querySelector('[data-petition-form]');
    var nav = document.querySelector('[data-stepper]');
    if (!form || !nav) return;
    var mq = window.matchMedia('(max-width: 767px)');
    function isMobile() { return mq.matches; }
    function steps() { return Array.prototype.slice.call(form.querySelectorAll('[data-form-step]')); }
    if (steps().length !== 3) return;
    var sticky = form.querySelector('[data-sticky-action]');
    var stickyBtn = sticky ? sticky.querySelector('button[type="submit"]') : null;
    var current = 0;
    var stepTpl = (form.dataset.stepLabel || 'Step {n} of 3').replace('{total}', '3');
    nav.classList.add('items-center', 'justify-center', 'gap-2', 'mb-6');
    nav.innerHTML = '<span class="text-sm text-muted tabular-nums" data-step-text></span>' +
        '<span class="flex-1 h-1 rounded-full bg-border overflow-hidden"><span class="block h-full bg-brand" data-step-bar style="width:33%"></span></span>';
    var stepText = nav.querySelector('[data-step-text]');
    var stepBar = nav.querySelector('[data-step-bar]');
    var backBtn = document.createElement('button');
    backBtn.type = 'button';
    backBtn.className = 'fcui-btn fcui-btn--secondary fcui-tap w-full mb-2';
    backBtn.textContent = form.dataset.backLabel || 'Back';
    if (sticky) sticky.insertBefore(backBtn, sticky.firstChild);
    var nextBtn = null;
    if (stickyBtn) {
        nextBtn = document.createElement('button');
        nextBtn.type = 'button';
        nextBtn.className = stickyBtn.className;
        nextBtn.textContent = form.dataset.nextLabel || 'Next';
        stickyBtn.parentNode.insertBefore(nextBtn, stickyBtn);
    }
    function show(i) {
        current = Math.max(0, Math.min(2, i));
        steps().forEach(function (s, idx) { s.hidden = isMobile() && idx !== current; });
        nav.classList.toggle('hidden', !isMobile());
        nav.classList.toggle('flex', isMobile());
        if (stepText) stepText.textContent = stepTpl.replace('{n}', String(current + 1));
        if (stepBar) stepBar.style.width = ((current + 1) / 3 * 100) + '%';
        var last = current === 2;
        if (nextBtn) nextBtn.hidden = !isMobile() || last;
        if (stickyBtn) stickyBtn.hidden = isMobile() && !last;
        if (backBtn) backBtn.hidden = !isMobile() || current === 0;
        if (isMobile()) steps()[current].scrollIntoView({ block: 'start' });
    }
    function validStep() {
        var fields = steps()[current].querySelectorAll('input,select,textarea');
        for (var k = 0; k < fields.length; k++) {
            if (fields[k].id === 'petition_description') continue;
            if (!fields[k].reportValidity()) return false;
        }
        return true;
    }
    if (nextBtn) nextBtn.addEventListener('click', function () { if (validStep()) show(current + 1); });
    backBtn.addEventListener('click', function () { show(current - 1); });
    mq.addEventListener ? mq.addEventListener('change', function () { show(current); }) : mq.addListener(function () { show(current); });
    var bad = form.querySelector('.is-invalid');
    if (bad) {
        steps().forEach(function (s, idx) { if (s.contains(bad)) current = idx; });
    }
    show(current);
});
