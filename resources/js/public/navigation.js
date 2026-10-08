export function init(ctx) {
    const prefersReducedMotion = ctx.prefersReducedMotion;

    document.querySelectorAll('.fc-sign-form a[href]').forEach(function (link) {
        if (link.dataset.fcPolicyBound === '1') return;
        const href = link.getAttribute('href') || '';
        if (href === '' || href.charAt(0) === '#' || href.toLowerCase().indexOf('javascript:') === 0) return;
        link.dataset.fcPolicyBound = '1';
        link.setAttribute('target', '_blank');
        link.setAttribute('rel', 'noopener noreferrer');
        link.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            window.open(link.href, '_blank', 'noopener,noreferrer');
        });
    });

    document.addEventListener('click', function (event) {
        const trigger = event.target.closest('[data-alert-dismiss]');
        if (!trigger) return;

        const alertBox = trigger.closest('[data-alert]');
        if (!alertBox) return;

        event.preventDefault();

        if (prefersReducedMotion) {
            alertBox.remove();
            return;
        }

        alertBox.setAttribute('data-alert-hiding', '');
        let removed = false;
        const finish = function () {
            if (removed) return;
            removed = true;
            alertBox.remove();
        };
        alertBox.addEventListener('transitionend', finish, { once: true });
        window.setTimeout(finish, 300);
    });

    const scrollTopBtn = document.querySelector('[data-scroll-top]');
    if (scrollTopBtn) {
        const toggleScrollTop = function () {
            if (window.scrollY > 400) {
                scrollTopBtn.classList.add('is-visible');
            } else {
                scrollTopBtn.classList.remove('is-visible');
            }
        };
        toggleScrollTop();
        window.addEventListener('scroll', toggleScrollTop, { passive: true });
        scrollTopBtn.addEventListener('click', function (event) {
            event.preventDefault();
            window.scrollTo({ top: 0, behavior: prefersReducedMotion ? 'auto' : 'smooth' });
        });
    }

    (function () {
        if (document.getElementById('fcui-topbar')) return;
        const bar = document.createElement('div');
        bar.id = 'fcui-topbar';
        bar.className = 'fcui-topbar';
        document.body.appendChild(bar);

        let active = false;
        let settleTimer = null;

        const start = function () {
            if (active) return;
            active = true;
            window.clearTimeout(settleTimer);
            if (prefersReducedMotion) {
                bar.style.transition = 'none';
                bar.classList.add('is-active');
                bar.style.width = '90%';
                return;
            }
            bar.classList.add('is-active');
            bar.style.width = '0%';
            window.requestAnimationFrame(function () {
                window.requestAnimationFrame(function () {
                    bar.style.transition = '';
                    bar.style.width = '90%';
                });
            });
        };

        const done = function () {
            if (!active) return;
            active = false;
            bar.style.width = '100%';
            settleTimer = window.setTimeout(function () {
                bar.classList.remove('is-active');
                bar.style.transition = 'none';
                bar.style.width = '0%';
                window.requestAnimationFrame(function () {
                    bar.style.transition = '';
                });
            }, prefersReducedMotion ? 0 : 220);
        };

        document.addEventListener('click', function (event) {
            const link = event.target.closest('a[href]');
            if (!link) return;
            if (link.hasAttribute('data-no-progress')) return;
            if (link.target === '_blank') return;
            const href = link.getAttribute('href') || '';
            if (href === '' || href.charAt(0) === '#' || href.toLowerCase().indexOf('javascript:') === 0) return;
            let url;
            try {
                url = new URL(link.href, window.location.href);
            } catch (error) {
                return;
            }
            if (url.origin !== window.location.origin) return;
            if (url.pathname === window.location.pathname && url.search === window.location.search && url.hash) return;
            start();
        });

        document.addEventListener('submit', function () {
            start();
        });

        window.addEventListener('pagehide', done);
    })();

    document.addEventListener('click', function (event) {
        const trigger = event.target.closest('[data-copy], [data-url]');
        if (!trigger) return;
        if (!trigger.hasAttribute('data-copy') && !trigger.hasAttribute('data-url')) return;
        const value = trigger.getAttribute('data-copy') || trigger.getAttribute('data-url');
        if (!value) return;

        event.preventDefault();

        const label = trigger.dataset.copiedLabel || 'Copied!';
        const onSuccess = function () {
            window.clientToast?.success?.(label);
        };
        const onFailure = function () {
            window.clientToast?.error?.(trigger.dataset.copyFailedLabel || 'Copy failed');
        };

        const fallbackCopy = function () {
            try {
                const temp = document.createElement('textarea');
                temp.value = value;
                temp.setAttribute('readonly', '');
                temp.style.position = 'absolute';
                temp.style.left = '-9999px';
                document.body.appendChild(temp);
                temp.select();
                const ok = document.execCommand('copy');
                document.body.removeChild(temp);
                if (ok) {
                    onSuccess();
                } else {
                    onFailure();
                }
            } catch (error) {
                onFailure();
            }
        };

        if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
            navigator.clipboard.writeText(value).then(onSuccess).catch(fallbackCopy);
        } else {
            fallbackCopy();
        }
    });

    (function () {
        const connection = navigator.connection || {};
        if (connection.saveData === true) return;
        const prefetched = new Set();
        const doPrefetch = function (anchor) {
            if (!anchor || !anchor.hasAttribute('data-prefetch')) return;
            let url;
            try {
                url = new URL(anchor.href, window.location.href);
            } catch (error) {
                return;
            }
            if (url.origin !== window.location.origin) return;
            if (prefetched.has(url.href)) return;
            prefetched.add(url.href);
            const link = document.createElement('link');
            link.rel = 'prefetch';
            link.href = url.href;
            document.head.appendChild(link);
        };
        document.addEventListener('mouseover', function (event) {
            const anchor = event.target.closest('a[data-prefetch]');
            if (anchor) doPrefetch(anchor);
        }, { passive: true });
        document.addEventListener('focusin', function (event) {
            const anchor = event.target.closest('a[data-prefetch]');
            if (anchor) doPrefetch(anchor);
        });
    })();
}
