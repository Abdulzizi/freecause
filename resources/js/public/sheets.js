export function init() {
    const body = document.body;
    const sheets = Array.from(document.querySelectorAll('dialog[data-sheet]'));

    if (!sheets.length) {
        return;
    }

    let lastTrigger = null;
    let openSheet = null;

    function lockScroll() {
        body.classList.add('fc-sheet-open');
    }

    function unlockScroll() {
        if (!Array.from(document.querySelectorAll('dialog[data-sheet][open]')).length) {
            body.classList.remove('fc-sheet-open');
        }
    }

    function show(sheet, trigger) {
        if (!sheet || sheet.open) {
            return;
        }

        lastTrigger = trigger || null;

        if (typeof sheet.showModal === 'function') {
            sheet.showModal();
        } else {
            sheet.setAttribute('open', 'open');
        }

        openSheet = sheet;
        lockScroll();

        const focusTarget = sheet.querySelector('[data-sheet-autofocus]') || sheet.querySelector('[data-sheet-close]');
        if (focusTarget) {
            focusTarget.focus({ preventScroll: true });
        }
    }

    function hide(sheet, options) {
        if (!sheet || !sheet.open) {
            return;
        }

        if (typeof sheet.close === 'function') {
            sheet.close();
        } else {
            sheet.removeAttribute('open');
        }

        openSheet = null;
        unlockScroll();

        if (!options || options.returnFocus !== false) {
            if (lastTrigger && document.contains(lastTrigger)) {
                lastTrigger.focus({ preventScroll: true });
            }
        }

        if (!options || options.popHistory !== false) {
            if (history.state && history.state.fcSheet === sheet.id) {
                history.back();
            }
        }
    }

    document.addEventListener('click', function (event) {
        const opener = event.target.closest('[data-sheet-open]');
        if (opener) {
            event.preventDefault();
            const sheet = document.getElementById(opener.dataset.sheetOpen);
            if (sheet) {
                history.pushState({ fcSheet: sheet.id }, '', location.href);
                show(sheet, opener);
            }
            return;
        }

        const closer = event.target.closest('[data-sheet-close]');
        if (closer) {
            event.preventDefault();
            hide(closer.closest('dialog[data-sheet]') || openSheet);
        }
    });

    sheets.forEach(function (sheet) {
        sheet.addEventListener('click', function (event) {
            if (event.target === sheet) {
                hide(sheet);
            }
        });

        sheet.addEventListener('close', function () {
            openSheet = null;
            unlockScroll();
            if (!history.state || history.state.fcSheet !== sheet.id) {
                return;
            }
            history.back();
        });

        sheet.addEventListener('cancel', function (event) {
            event.preventDefault();
            hide(sheet, { popHistory: false });
        });

        const grip = sheet.querySelector('[data-sheet-grip]');
        if (grip) {
            let startY = null;

            sheet.addEventListener('touchstart', function (event) {
                startY = event.touches[0].clientY;
            }, { passive: true });

            sheet.addEventListener('touchend', function (event) {
                if (startY === null) {
                    return;
                }
                const delta = event.changedTouches[0].clientY - startY;
                startY = null;
                if (delta > 90) {
                    hide(sheet);
                }
            }, { passive: true });
        }
    });

    window.addEventListener('popstate', function () {
        if (openSheet) {
            hide(openSheet, { popHistory: false, returnFocus: false });
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && openSheet) {
            hide(openSheet);
        }
    });
}
