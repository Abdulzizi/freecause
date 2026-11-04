import { test, expect, uniq, db, adminLogin, confirmDialog } from './helpers.js';

test.describe.configure({ mode: 'serial' });

const title = uniq('E2E Admin Petition ');
let id;

test('admin creates a petition from the list', async ({ page }) => {
    await adminLogin(page);
    await page.goto('/admin/petitions');
    await page.getByRole('button', { name: /new petition/i }).click();
    const form = page.locator('form[action$="/admin/petitions/create"]');
    await form.locator('input[name=title]').fill(title);
    await form.locator('select[name=category_id]').selectOption({ index: 1 });
    await form.locator('select[name=goal_signatures]').selectOption({ index: 1 });
    await form.getByRole('button', { name: /^create/i }).click();
    await expect.poll(() => db(`select petition_id from petition_translations where title='${title}'`)).not.toBe('');
    id = db(`select petition_id from petition_translations where title='${title}'`);
    await expect(page).toHaveURL(new RegExp(`select=${id}`));
});

test('admin edits the petition in the drawer', async ({ page }) => {
    await adminLogin(page);
    await page.goto(`/admin/petitions?select=${id}&locale=en`);
    const form = page.locator('form[action$="/admin/petitions/save"]');
    await form.locator('input[name=target]').fill('E2E Target Body');
    await form.locator('.fc-drawer-savebar [type=submit]').click();
    await expect.poll(() => db(`select target from petitions where id=${id}`)).toBe('E2E Target Body');
});

test('admin toggles active and featured from the row', async ({ page }) => {
    await adminLogin(page);
    await page.goto(`/admin/petitions?id=${id}`);
    await page.locator(`[data-petition-toggle][data-id="${id}"][data-kind=active]`).click();
    await expect.poll(() => db(`select is_active from petitions where id=${id}`)).toBe('0');
    await page.locator(`[data-petition-toggle][data-id="${id}"][data-kind=featured]`).click();
    await expect.poll(() => db(`select count(*) from petition_translations where petition_id=${id} and is_featured=1`)).toBe('1');
});

test('admin bulk-deletes the petition after confirming', async ({ page }) => {
    await adminLogin(page);
    await page.goto(`/admin/petitions?id=${id}`);
    await page.locator('tbody input[type=checkbox]').first().check();
    await page.locator('#bulk-delete').click();
    await confirmDialog(page).click();
    await expect.poll(() => db(`select count(*) from petitions where id=${id}`)).toBe('0');
});
