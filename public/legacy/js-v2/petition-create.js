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
