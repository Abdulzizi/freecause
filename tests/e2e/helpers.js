import { test as base, expect } from '@playwright/test';

const ORIGIN = 'http://127.0.0.1:8099';

export const test = base.extend({
    page: async ({ page }, use) => {
        const errors = [];
        await page.route('**/*', (route) =>
            route.request().url().startsWith(ORIGIN) || route.request().url().startsWith('data:') ? route.continue() : route.abort(),
        );
        page.on('pageerror', (e) => {
            if (!e.message.includes('ViewTransition')) errors.push(`pageerror: ${e.message}`);
        });
        page.on('console', (m) => {
            if (m.type() === 'error' && !m.text().includes('net::ERR_FAILED')) errors.push(`console: ${m.text()}`);
        });
        page.on('response', (r) => {
            if (r.url().startsWith(ORIGIN) && r.status() >= 500) errors.push(`${r.status()}: ${r.url()}`);
        });
        await use(page);
        expect(errors).toEqual([]);
    },
});

export { expect };

export const PETITION = '/en/petition/protect-the-city-park/1';

export async function adminLogin(page) {
    await page.goto('/admin/login');
    await page.locator('[name=login]').fill('admin@e2e.test');
    await page.locator('[name=password]').fill('e2e-password');
    await page.locator('[name=password]').press('Enter');
    await expect(page).toHaveURL(/\/admin(\/|$)/);
    await expect(page).not.toHaveURL(/login/);
}
