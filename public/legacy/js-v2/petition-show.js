document.addEventListener('DOMContentLoaded', () => {
    const copyButton = document.getElementById('copyPetitionLink');

    copyButton?.addEventListener('click', async () => {
        const url = copyButton.dataset.url;

        try {
            await navigator.clipboard.writeText(url);
        } catch (error) {
            const input = document.createElement('textarea');
            input.value = url;
            input.style.position = 'fixed';
            input.style.opacity = '0';
            document.body.appendChild(input);
            input.select();
            document.execCommand('copy');
            input.remove();
        }

        copyButton.innerHTML = `<i class="fa fa-check"></i> ${copyButton.dataset.copiedLabel}`;
        copyButton.classList.add('fc-share-copy--copied');
    });

    const toggle = document.getElementById('widgetsToggle');
    const body = document.getElementById('widgetsBody');
    if (!toggle || !body || !window.bootstrap) return;

    body.addEventListener('shown.bs.collapse', () => {
        toggle.removeAttribute('data-bs-toggle');
        toggle.removeAttribute('data-bs-target');
        toggle.removeAttribute('aria-controls');
        toggle.style.cursor = 'default';
    }, { once: true });
});
