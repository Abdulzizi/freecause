import { test, expect, db, adminLogin } from './helpers.js';

test('email translation submit retains its named action with admin loading handlers active', async ({ page }) => {
    const original = db("select hex(value) from settings where key='deepl_api_key' and \"group\"='global'");
    try {
        db("insert into settings (\"group\", key, value, created_at, updated_at) values ('global', 'deepl_api_key', '\"synthetic-e2e-not-a-provider-key\"', datetime('now'), datetime('now')) on conflict(\"group\", key) do update set value=excluded.value");
        await adminLogin(page);
        await page.goto('/admin/options/language?locale=en');
        await page.route('**/admin/options/language', async (route) => {
            if (route.request().method() !== 'POST') return route.continue();
            await route.fulfill({ status: 200, contentType: 'text/html', body: '<p>Submission captured</p>' });
        });
        const request = page.waitForRequest((request) => request.method() === 'POST' && new URL(request.url()).pathname === '/admin/options/language');
        await page.getByRole('button', { name: 'Save, translate and sync all email languages' }).click();
        const submitted = new URLSearchParams((await request).postData());
        expect(submitted.get('translate_all')).toBe('1');
        expect(submitted.get('locale')).toBe('en');
    } finally {
        db(original ? `update settings set value=cast(x'${original}' as text) where key='deepl_api_key' and "group"='global'` : "delete from settings where key='deepl_api_key' and \"group\"='global'");
    }
});
