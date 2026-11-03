import { test, expect, db, adminLogin } from './helpers.js';

test('admin saves ads.txt and /ads.txt serves it, then the original is restored', async ({ page }) => {
    const existed = db("select count(*) from settings where key='ads_txt'") === '1';
    // hex() keeps quotes and newlines intact for the restore.
    const original = db("select hex(value) from settings where key='ads_txt'");
    const line = 'google.com, pub-0000000000000000, DIRECT, f08c47fec0942fa0';
    try {
        await adminLogin(page);
        await page.goto('/admin/ads');
        const field = page.locator('textarea[name=ads_txt]');
        await field.fill(line);
        await page.getByRole('button', { name: 'save' }).click();
        await expect.poll(() => db("select value from settings where key='ads_txt'")).toBe(line);
        await expect(field).toHaveValue(line);

        const res = await page.request.get('/ads.txt');
        expect(res.headers()['content-type']).toContain('text/plain');
        expect(await res.text()).toBe(line);
    } finally {
        db(existed ? `update settings set value=cast(x'${original}' as text) where key='ads_txt'` : "delete from settings where key='ads_txt'");
    }
});
