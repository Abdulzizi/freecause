import { test, expect, uniq, db, adminLogin, confirmDialog } from './helpers.js';

test('admin adds a user level and deletes it; levels in use cannot be selected', async ({ page }) => {
    const name = uniq('e2e_level_');
    await adminLogin(page);
    await page.goto('/admin/system/user-levels');
    const form = page.locator('form[action$="/admin/system/user-levels"]');
    await form.locator('input[name=name]').fill(name);
    await form.locator('input[name=visible_name]').fill('E2E Level');
    await form.locator('[type=submit]').click();
    await expect.poll(() => db(`select visible_name||'|'||is_system from user_levels where name='${name}'`)).toBe('E2E Level|0');

    await expect(page.locator('tbody tr', { hasText: 'moderator' }).locator('input.bulk-checkbox')).toHaveCount(0);
    const id = db(`select id from user_levels where name='${name}'`);
    await page.locator(`tbody input.bulk-checkbox[value="${id}"]`).check();
    await page.locator('.bulk-action[data-action=delete]').click();
    await confirmDialog(page).click();
    await expect.poll(() => db(`select count(*) from user_levels where name='${name}'`)).toBe('0');
});

test('admin user info refuses a taken email and a wrong current password, and saves a new email', async ({ page }) => {
    const email = () => db("select email from users where name='e2e-admin'");
    const save = async (fields) => {
        const form = page.locator('form[action$="/admin/system/user-info"]');
        for (const [field, value] of Object.entries(fields)) await form.locator(`input[name=${field}]`).fill(value);
        await form.getByRole('button', { name: 'save' }).click();
        await confirmDialog(page).click();
    };
    try {
        await adminLogin(page);
        await page.goto('/admin/system/user-info');
        await expect(page.locator('form[action$="/admin/system/user-info"]').getByText('e2e-admin', { exact: true })).toBeVisible();

        await save({ email: 'owner@e2e.test' });
        await expect(page.getByText('The email has already been taken.')).toBeVisible();
        expect(email()).toBe('admin@e2e.test');

        await save({ email: 'admin@e2e.test', current_password: 'wrong-password', new_password: 'e2e-new-pass-1', new_password_confirmation: 'e2e-new-pass-1' });
        await expect(page.getByText('Current password is incorrect.')).toBeVisible();
        await expect(page).toHaveURL(/\/admin\/system\/user-info/);

        const changed = `${uniq('admin')}@e2e.test`;
        await save({ email: changed, current_password: 'e2e-password' });
        await expect.poll(email).toBe(changed);
        await page.goto('/admin/system/user-info');
        await save({ email: 'admin@e2e.test', current_password: 'e2e-password' });
        await expect.poll(email).toBe('admin@e2e.test');
    } finally {
        db("update users set email='admin@e2e.test' where name='e2e-admin'");
    }
});
