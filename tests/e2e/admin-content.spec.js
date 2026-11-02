import { test, expect, uniq, db, adminLogin } from './helpers.js';

test('admin site-settings announcement saves and shows on the public site', async ({ page, browser }) => {
    const text = uniq('E2E announcement ');
    const setAnnouncement = async (active, value) => {
        await page.goto('/admin/options/global');
        await page.locator('input[type=checkbox][name=announcement_active]').setChecked(active);
        if (value !== undefined) await page.locator('input[name=announcement_text]').fill(value);
        await page.getByRole('button', { name: /^save/i }).click();
        await expect.poll(() => db(`select value from settings where key='announcement_active'`)).toBe(active ? 'true' : 'false');
    };
    await adminLogin(page);
    try {
        await setAnnouncement(true, text);
        expect(db(`select value from settings where key='announcement_text'`)).toBe(text);
        const visitor = await browser.newPage();
        await visitor.goto('/en');
        await expect(visitor.getByText(text).first()).toBeVisible();
        await visitor.close();
    } finally {
        await setAnnouncement(false);
    }
});

test('admin edits a content page, it is public under its new slug, then restores it', async ({ page, browser }) => {
    const title = uniq('E2E Page Title ');
    const original = {
        title: db(`select title from page_translations where page_id=8 and locale='en'`),
        slug: db(`select slug from page_translations where page_id=8 and locale='en'`),
    };
    await adminLogin(page);
    const save = async (values) => {
        await page.goto('/admin/pages?select=8&locale=en');
        const form = page.locator('form[action$="/admin/pages/save"]');
        await form.locator('input[name=title]').fill(values.title);
        await form.locator('input[name=slug]').fill(values.slug);
        await form.getByRole('button', { name: /^save/i }).click();
        await expect.poll(() => db(`select title from page_translations where page_id=8 and locale='en'`)).toBe(values.title);
    };

    try {
        await save({ title, slug: 'e2e-edited-page' });
        const visitor = await browser.newPage();
        await visitor.goto('/en/e2e-edited-page');
        await expect(visitor.locator('h1').first()).toContainText(title);
        await visitor.close();
    } finally {
        await save(original);
    }
});

test('admin saves review keywords for a locale', async ({ page }) => {
    const term = uniq('e2eterm');
    await adminLogin(page);
    await page.goto('/admin/keywords?locale=en');
    const form = page.locator('form[action$="/admin/keywords/save"]');
    const current = await form.locator('textarea[name=terms]').inputValue();
    await form.locator('textarea[name=terms]').fill(`${current}\n${term}`);
    await form.getByRole('button', { name: /^save/i }).click();
    await expect.poll(() => db(`select count(*) from settings where value like '%${term}%'`)).toBe('1');
});
