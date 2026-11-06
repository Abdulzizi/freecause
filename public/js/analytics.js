window.dataLayer = window.dataLayer || [];
window.gtag = window.gtag || function () { window.dataLayer.push(arguments); };

function gtagInit() {
    gtag('js', new Date());
    gtag('config', 'G-9CYCSY3PWD', { allow_google_signals: false, allow_ad_personalization_signals: false });
}

if (document.prerendering) {
    document.addEventListener('prerenderingchange', gtagInit, { once: true });
} else {
    gtagInit();
}
