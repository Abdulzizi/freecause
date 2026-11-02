import { test, expect, adminLogin } from './helpers.js';

test('a restricted admin only reaches the modules it has permission for', async ({ page }) => {
    await adminLogin(page, 'mod@e2e.test');
    expect((await page.request.get('/admin/petitions')).status()).toBe(200);
    expect((await page.request.get('/admin/users')).status()).toBe(403);
    expect((await page.request.get('/admin/options/global')).status()).toBe(403);
    expect((await page.request.get('/admin/system/permissions')).status()).toBe(403);
});

test('a restricted admin cannot change data without edit permission', async ({ page }) => {
    await adminLogin(page, 'mod@e2e.test');
    await page.goto('/admin/petitions');
    const token = await page.locator('meta[name=csrf-token]').getAttribute('content');
    const res = await page.request.post('/admin/petitions/bulk-action', { headers: { 'X-CSRF-TOKEN': token, Accept: 'application/json' }, data: { action: 'deactivate', ids: [1] } });
    expect(res.status()).toBe(403);
});

test('the admin 403 page renders one clean document', async ({ page }) => {
    await adminLogin(page, 'mod@e2e.test');
    const html = await (await page.request.get('/admin/users')).text();
    expect(html.match(/<html/g)).toHaveLength(1);
    expect(html).toContain('403 - ');
});
