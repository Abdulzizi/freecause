function setupSidebar() {
    const sidebar = document.getElementById('fc-menu');
    const backdrop = document.getElementById('fc-menu-backdrop');
    const toggle = document.querySelector('.fc-menu-toggle');
    let lastFocused = null;
    function close() {
        if (!sidebar || !backdrop) return;
        sidebar.classList.remove('show');
        backdrop.classList.remove('show');
        toggle?.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
        lastFocused?.focus?.();
    }
    function open() {
        if (!sidebar || !backdrop) return;
        lastFocused = document.activeElement;
        sidebar.classList.add('show');
        backdrop.classList.add('show');
        toggle?.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
        sidebar.focus();
    }
    document.addEventListener('click', (event) => {
        if (event.target.closest('[data-admin-sidebar-toggle]')) {
            if (sidebar?.classList.contains('show')) close(); else open();
        }
        if (event.target.closest('[data-admin-sidebar-close]')) close();
        const groupTitle = event.target.closest('.fc-group .group-title');
        if (groupTitle) {
            const group = groupTitle.closest('.fc-group');
            const collapsed = !group.classList.contains('collapsed');
            group.classList.toggle('collapsed', collapsed);
            group.querySelectorAll(':scope > a').forEach((link) => {
                if (collapsed) link.style.setProperty('display', 'none', 'important');
                else link.style.removeProperty('display');
            });
            const label = (groupTitle.textContent || '').trim().toLowerCase().replace(/\s+/g, '-');
            try { localStorage.setItem(`fc-group-${label}`, collapsed ? '1' : '0'); } catch (_) {}
        }
        if (event.target.closest('#fc-back-top')) window.scrollTo({ top: 0, behavior: 'smooth' });
        if (event.target.closest('.fc-menu a') && window.innerWidth <= 768) close();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && sidebar?.classList.contains('show')) close();
    });
    if (sidebar) {
        sidebar.querySelectorAll('.fc-group').forEach((group, index) => {
            const title = group.querySelector('.group-title');
            if (!title) return;
            const label = (title.textContent || '').trim().toLowerCase().replace(/\s+/g, '-');
            try {
                if (localStorage.getItem(`fc-group-${label || index}`) === '1') {
                    group.classList.add('collapsed');
                    group.querySelectorAll(':scope > a').forEach((link) => link.style.setProperty('display', 'none', 'important'));
                }
            } catch (_) {}
        });
    }
    const backTop = document.getElementById('fc-back-top');
    if (backTop) window.addEventListener('scroll', () => { backTop.style.display = window.scrollY > 400 ? 'flex' : 'none'; });
}

export function init() {
    setupSidebar();
    const backTop = document.getElementById('fc-back-top');
    if (backTop) backTop.style.display = window.scrollY > 400 ? 'flex' : 'none';
}
