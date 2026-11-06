import { test, expect, db } from './helpers.js';

let originalAds;
test.beforeEach(async ({ page }) => {
    originalAds = db("select hex(value) from settings where key='ads_enabled' and \"group\"='global'");
    db("insert into settings (\"group\", key, value, created_at, updated_at) values ('global', 'ads_enabled', 'true', datetime('now'), datetime('now')) on conflict(\"group\", key) do update set value=excluded.value");
    await page.context().clearCookies();
    await page.route('https://www.googletagmanager.com/**', (route) => route.fulfill({ status: 200, contentType: 'application/javascript', body: '/* consent test: no external analytics */' }));
    await page.route('**/ads/frame/**', (route) => route.fulfill({ status: 200, contentType: 'text/html', body: '<p>Test advertisement</p>' }));
});
test.afterEach(() => {
    db(originalAds ? `update settings set value=cast(x'${originalAds}' as text) where key='ads_enabled' and "group"='global'` : "delete from settings where key='ads_enabled' and \"group\"='global'");
});

function requestsFor(page) {
    const requests = [];
    page.on('request', (request) => {
        if (/googletagmanager\.com\/gtag\/js|\/ads\/frame\/|adsbygoogle\.js/.test(request.url())) requests.push(request.url());
    });
    return requests;
}

test('reject is remembered and optional services never load on subsequent pages', async ({ page }) => {
    const requests = requestsFor(page);
    await page.goto('/en');
    await expect(page.getByRole('button', { name: 'Reject optional', exact: true })).toBeVisible();
    expect(requests).toEqual([]);
    await expect(page.locator('iframe[data-fc-ad-src]').first()).toBeAttached();
    await page.getByRole('button', { name: 'Reject optional', exact: true }).click();
    await expect(page.locator('#cc-main .cm')).not.toBeVisible();
    await page.goto('/en/petitions');
    await expect(page.locator('[data-fc-cookie-settings]')).toHaveText('Cookie settings');
    await expect(page.locator('#cc-main .cm')).not.toBeVisible();
    expect(requests).toEqual([]);
});

test('analytics can be accepted independently and revoked without losing the page', async ({ page }) => {
    const requests = requestsFor(page);
    await page.goto('/en');
    await page.getByRole('button', { name: 'Preferences', exact: true }).click();
    await page.locator('#cc-main input[name="cc-analytics"]').first().check();
    await page.getByRole('button', { name: 'Save preferences', exact: true }).click();
    await expect.poll(() => requests.filter((url) => url.includes('googletagmanager.com')).length).toBe(1);
    expect(requests.some((url) => url.includes('/ads/frame/'))).toBe(false);
    await page.context().addCookies([{ name: '_ga', value: 'synthetic-consent-test', url: 'http://127.0.0.1:8099/' }]);
    await page.locator('[data-fc-cookie-settings]').click();
    await page.getByRole('button', { name: 'Reject optional', exact: true }).last().click();
    await expect(page).toHaveURL(/\/en$/);
    await expect.poll(async () => (await page.context().cookies()).some((cookie) => cookie.name === '_ga')).toBe(false);
    await page.reload();
    await expect(page.locator('[data-fc-cookie-settings]')).toBeVisible();
    expect(requests.filter((url) => url.includes('googletagmanager.com')).length).toBe(1);
});

test('accept all loads Adsterra only after choice and revocation unloads its frames', async ({ page }) => {
    const requests = requestsFor(page);
    await page.goto('/en');
    await expect(page.getByRole('button', { name: 'Accept all', exact: true })).toBeVisible();
    expect(requests).toEqual([]);
    await page.getByRole('button', { name: 'Accept all', exact: true }).click();
    await expect.poll(() => requests.some((url) => url.includes('/ads/frame/'))).toBe(true);
    await expect(page.locator('iframe[data-fc-ad-src][src]').first()).toBeAttached();
    expect(requests.some((url) => url.includes('adsbygoogle.js'))).toBe(false);
    await page.locator('[data-fc-cookie-settings]').click();
    await page.getByRole('button', { name: 'Reject optional', exact: true }).last().click();
    await expect(page.locator('iframe[data-fc-ad-src][src]')).toHaveCount(0);
});

test('Italian mobile banner fits the screen and offers an immediate refusal', async ({ page }) => {
    await page.setViewportSize({ width: 320, height: 740 });
    await page.goto('/it');
    await expect(page.getByRole('button', { name: 'Rifiuta facoltativi', exact: true })).toBeVisible();
    expect(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).toBe(true);
    await page.screenshot({ path: 'test-results/consent-mobile.png' });
    await page.getByRole('button', { name: 'Rifiuta facoltativi', exact: true }).click();
    await expect(page.locator('[data-fc-cookie-settings]')).toHaveText('Impostazioni cookie');
});
