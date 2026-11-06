/* Essential first-party consent setup. Optional tags remain inert until a choice. */
window.dataLayer = window.dataLayer || [];
window.gtag = window.gtag || function () {
    if (arguments[0] === 'event' && !window.FCAnalyticsAllowed) return;
    window.dataLayer.push(arguments);
};
window.gtag('consent', 'default', {
    analytics_storage: 'denied',
    ad_storage: 'denied',
    ad_user_data: 'denied',
    ad_personalization: 'denied',
    functionality_storage: 'granted',
    security_storage: 'granted',
});
document.documentElement.dataset.fcAds = 'off';
