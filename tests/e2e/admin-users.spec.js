import { test, expect, uniq, db, adminLogin, register, login, setRequireVerification, confirmDialog } from './helpers.js';

test.describe.configure({ mode: 'serial' });

let email;
let id;

test.beforeAll(async ({ browser }) => {
    const page = await browser.newPage();
    await setRequireVerification(page, false);
    email = `${uniq('adm')}@e2e.test`;
    await register(page, { email });
    await page.close();
    id = db(`select id from users where email='${email}'`);
});

test('admin edits a user in the drawer', async ({ page }) => {
    await adminLogin(page);
    await page.goto(`/admin/users?select=${id}`);
    const form = page.locator('form[action$="/admin/users/save"]');
    await form.locator('input[name=first_name]').fill('Edited');
    await form.getByRole('button', { name: /^save/i }).click();
    await expect.poll(() => db(`select first_name from users where id=${id}`)).toBe('Edited');
});

test('admin bans a user from the drawer, the user is locked out, then unbanned', async ({ page, browser }) => {
    const level = () => db(`select ul.name from users u join user_levels ul on ul.id=u.level_id where u.id=${id}`);
    await adminLogin(page);
    await page.goto(`/admin/users?select=${id}`);
    await page.getByRole('button', { name: 'Ban user' }).click();
    await page.locator('.swal2-confirm').click();
    await confirmDialog(page).click();
    await expect.poll(level).toBe('banned');

    const banned = await browser.newPage();
    await login(banned, email);
    await banned.goto('/en/profile');
    await expect(banned).toHaveURL(/\/login|\/en\/?$/);
    await banned.close();

    await page.goto(`/admin/users?select=${id}`);
    await page.getByRole('button', { name: 'Unban user' }).click();
    await confirmDialog(page).click();
    await expect.poll(level).toBe('user');
});

test('admin deletes a user after confirming', async ({ page }) => {
    await adminLogin(page);
    await page.goto(`/admin/users?id=${id}`);
    await page.locator(`tbody input.bulk-checkbox[value="${id}"]`).check();
    await page.locator('#bulk-delete').click();
    await confirmDialog(page).click();
    await expect.poll(() => db(`select count(*) from users where id=${id}`)).toBe('0');
});
