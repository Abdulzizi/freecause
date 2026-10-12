window.dataLayer = window.dataLayer || [];
function gtag() { dataLayer.push(arguments); }

function gtagInit() {
    gtag('js', new Date());
    gtag('config', 'G-9CYCSY3PWD');
}

if (document.prerendering) {
    document.addEventListener('prerenderingchange', gtagInit, { once: true });
} else {
    gtagInit();
}
