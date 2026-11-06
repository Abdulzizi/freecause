/* First-party aggregate page-load counter. No cookies, identifiers or URLs sent. */
(function () {
    'use strict';
    if (window.FCAggregateCounted || window.top !== window.self || navigator.doNotTrack === '1' || navigator.globalPrivacyControl === true) return;
    function count() {
        if (window.FCAggregateCounted || document.prerendering || document.visibilityState !== 'visible') return;
        window.FCAggregateCounted = true;
        document.removeEventListener('visibilitychange', count);
        const segments = location.pathname.split('/').filter(Boolean);
        const magazine = segments[0] === 'magazine';
        const part = segments[1] || '';
        const area = magazine ? 'magazine' : !part ? 'home' : part === 'petitions' ? 'petitions' : part === 'petition' ? 'petition' : part === 'user' ? 'profile' : 'other';
        const supported = ['da', 'de', 'el', 'en', 'es', 'fr', 'it', 'nl', 'pl', 'pt', 'ro', 'ru', 'sv', 'tr'];
        const language = document.documentElement.lang.toLowerCase().split(/[-_]/)[0];
        const locale = supported.includes(language) ? language : 'en';
        const ua = navigator.userAgent;
        const device = /iPad|Tablet/i.test(ua) ? 'tablet' : /Mobile|Android|iPhone/i.test(ua) ? 'mobile' : 'desktop';
        fetch('/site-metrics', {
            method: 'POST', credentials: 'omit', referrerPolicy: 'no-referrer', keepalive: true,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ area, locale, device, automated: navigator.webdriver === true }),
        }).catch(function () { /* Measurement must never interrupt the page. */ });
    }
    document.addEventListener('visibilitychange', count);
    document.addEventListener('prerenderingchange', count, { once: true });
    count();
})();
