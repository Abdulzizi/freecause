import { test, expect, db, newUser, expectLoginRefused, PASSWORD } from './helpers.js';

async function deleteAccount(page, password) {
    await page.goto('/en/profile');
    const form = page.locator('form[data-account-delete-form]');
    await form.locator('#delete_password').fill(password);
    await form.locator('input[name=confirm_delete]').check();
    await form.locator('[data-account-delete-submit]').click();
    await page.locator('.swal2-confirm').click();
}

test('a wrong password keeps the account; the right one deletes it and the login stops working', async ({ page }) => {
    const email = await newUser(page, 'del');
    const exists = () => db(`select count(*) from users where email='${email}'`);

    await deleteAccount(page, 'not-my-password');
    await expect(page).toHaveURL(/\/en\/profile/);
    await expect(page.locator('[data-alert-variant=error]').first()).toBeAttached();
    expect(exists()).toBe('1');

    await deleteAccount(page, PASSWORD);
    await expect(page).toHaveURL(/\/en\/?$/);
    await expect.poll(exists).toBe('0');

    await expectLoginRefused(page, email);
    expect(exists()).toBe('0');
});
