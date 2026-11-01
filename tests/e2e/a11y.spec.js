import AxeBuilder from '@axe-core/playwright';
import { test, expect, PETITION, adminLogin } from './helpers.js';

const PUBLIC = ['/en', '/en/petitions', PETITION, '/en/create-petition', '/en/login', '/en/register', '/__components'];
const ADMIN = ['/admin/dashboard', '/admin/petitions', '/admin/petitions?select=1&locale=en'];
const WIDTHS = [375, 1280];

async function check(page, path, width) {
    await page.setViewportSize({ width, height: 800 });
    await page.goto(path);
    const overflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
    expect(overflow, `horizontal overflow on ${path} at ${width}px`).toBeLessThanOrEqual(0);
    const { violations } = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa']).analyze();
    const blocking = violations
        .filter((v) => ['serious', 'critical'].includes(v.impact))
        .map((v) => `${v.id}: ${v.nodes.slice(0, 3).map((n) => n.target.join(' ')).join(' , ')}`);
    expect(blocking, `${path} at ${width}px`).toEqual([]);
}

for (const width of WIDTHS) {
    for (const path of PUBLIC) {
        test(`public ${path} at ${width}px`, async ({ page }) => check(page, path, width));
    }
    for (const path of ADMIN) {
        test(`admin ${path} at ${width}px`, async ({ page }) => {
            await adminLogin(page);
            await check(page, path, width);
        });
    }
}
