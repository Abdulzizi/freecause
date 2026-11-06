import { test, expect, db } from './helpers.js';

test('an optional-cookie rejection still permits one anonymous page-load report', async ({ page }) => {
    await page.context().addCookies([{ name: 'fc_test_session', value: 'synthetic', url: 'http://127.0.0.1:8099' }]);
    const optional = [];
    page.on('request', (request) => {
        if (/googletagmanager\.com|\/ads\/frame\//.test(request.url())) optional.push(request.url());
    });
    const responsePromise = page.waitForResponse((response) => response.url().endsWith('/site-metrics'));
    await page.goto('/it/petitions');
    const response = await responsePromise;
    expect(response.status()).toBe(204);
    const request = response.request();
    expect(request.postDataJSON()).toEqual({ area: 'petitions', locale: 'it', device: 'desktop', automated: true });
    const headers = await request.allHeaders();
    expect(headers.cookie).toBeUndefined();
    expect(headers.referer).toBeUndefined();
    expect(optional).toEqual([]);
    expect(Number(db("select coalesce(sum(page_loads),0) from aggregate_traffic where locale='it' and area='petitions'"))).toBeGreaterThan(0);
});

test('Do Not Track prevents the client report', async ({ page }) => {
    await page.addInitScript(() => Object.defineProperty(Navigator.prototype, 'doNotTrack', { get: () => '1' }));
    const reports = [];
    page.on('request', (request) => { if (request.url().endsWith('/site-metrics')) reports.push(request.url()); });
    await page.goto('/en');
    await expect(page.locator('[data-fc-cookie-settings]')).toBeVisible();
    expect(reports).toEqual([]);
});

test('a prerender is counted once only when activated', async ({ page }) => {
    await page.addInitScript(() => Object.defineProperty(document, 'prerendering', { get: () => window.FCTestActivated !== true }));
    const reports = [];
    page.on('request', (request) => { if (request.url().endsWith('/site-metrics')) reports.push(request.url()); });
    await page.goto('/en');
    expect(reports).toEqual([]);
    const responsePromise = page.waitForResponse((response) => response.url().endsWith('/site-metrics'));
    await page.evaluate(() => {
        window.FCTestActivated = true;
        document.dispatchEvent(new Event('prerenderingchange'));
        document.dispatchEvent(new Event('visibilitychange'));
    });
    expect((await responsePromise).status()).toBe(204);
    expect(reports).toHaveLength(1);
});
