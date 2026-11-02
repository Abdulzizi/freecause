import { test, expect, uniq, db, adminLogin, confirmDialog } from './helpers.js';

test.describe.configure({ mode: 'serial' });

const name = uniq('E2E Cat ');
let id;

test('admin creates and renames a category', async ({ page }) => {
    await adminLogin(page);
    await page.goto('/admin/categories');
    const create = page.locator('form[action$="/admin/categories/create"]');
    await create.locator('input[name=name]').fill(name);
    await create.getByRole('button', { name: /^create/i }).click();
    await expect.poll(() => db(`select category_id from category_translations where name='${name}'`)).not.toBe('');
    id = db(`select category_id from category_translations where name='${name}'`);

    await page.goto(`/admin/categories?select=${id}`);
    const save = page.locator('form[action$="/admin/categories/save"]');
    await save.locator('input[name=name]').fill(`${name} v2`);
    await save.getByRole('button', { name: /^save/i }).click();
    await expect.poll(() => db(`select name from category_translations where category_id=${id} and locale='en'`)).toBe(`${name} v2`);
});

test('admin deletes the category', async ({ page }) => {
    await adminLogin(page);
    await page.goto(`/admin/categories?select=${id}`);
    await page.getByRole('button', { name: 'delete category' }).click();
    await confirmDialog(page).click();
    await expect.poll(() => db(`select count(*) from categories where id=${id}`)).toBe('0');
});
