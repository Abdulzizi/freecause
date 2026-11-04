import { test, expect, db, adminLogin } from './helpers.js';

// Every lang/ directory is already a seeded language, so free one up (Swedish) and recreate it through the UI.
// Other specs and the screenshots need the seeded set and English as default: always put both back.
const restore = () => {
    db("insert into languages (code, name, is_active, is_default, created_at, updated_at) select 'sv', 'Swedish', 1, 0, datetime('now'), datetime('now') where not exists (select 1 from languages where code='sv')");
    db("update languages set name='Swedish', is_active=1 where code='sv'");
    db("update languages set is_default=(code='en')");
};

const row = (page, code) => page.locator('div[x-data="{ editing: false }"]', { hasText: `${code}: ` });
const lang = (code) => db(`select name||'|'||is_active||'|'||is_default from languages where code='${code}'`);

test('admin creates, edits and sets a default language, then English is default again', async ({ page }) => {
    try {
        db("delete from languages where code='sv'");
        await adminLogin(page);
        await page.goto('/admin/languages');

        const create = page.locator('form[action$="/admin/languages"]');
        await create.locator('input[name=code]').fill('vendor');
        await create.locator('input[name=name]').fill('Vendor');
        await create.locator('[type=submit]').click();
        await expect(page.getByText('Missing translation directory lang/vendor.')).toBeVisible();
        expect(db("select count(*) from languages where code='vendor'")).toBe('0');

        await create.locator('input[name=code]').fill('sv');
        await create.locator('input[name=name]').fill('Swedish E2E');
        await create.locator('[type=submit]').click();
        await expect.poll(() => lang('sv')).toBe('Swedish E2E|1|0');

        await row(page, 'SV').getByRole('button', { name: 'edit' }).click();
        await row(page, 'SV').locator('input[name=name]').fill('Swedish');
        await row(page, 'SV').getByRole('button', { name: 'save' }).click();
        await expect.poll(() => lang('sv')).toBe('Swedish|1|0');

        await row(page, 'SV').getByRole('button', { name: 'set default' }).click();
        await expect.poll(() => lang('sv')).toBe('Swedish|1|1');
        expect(lang('en')).toBe('English|1|0');
        const legacy = await page.request.get('/petitions', { maxRedirects: 0 });
        expect(legacy.headers().location).toMatch(/\/sv\/petitions$/);

        await row(page, 'EN').getByRole('button', { name: 'set default' }).click();
        await expect.poll(() => lang('en')).toBe('English|1|1');
        expect(lang('sv')).toBe('Swedish|1|0');
    } finally {
        restore();
    }
});
