import { test, expect, uniq, db, newUser } from './helpers.js';

const prefs = (email) => db(`select notify_milestones||notify_new_signatures||notify_product||'|'||(unsubscribed_at is not null) from users where email='${email}'`);

test('a user saves notification preferences, then the unsubscribe link turns everything off', async ({ page }) => {
    const email = await newUser(page, 'prefs');
    // Optional mail is opt-in: new accounts start with everything off.
    expect(prefs(email)).toBe('000|0');

    await page.goto('/en/preferences');
    const form = page.locator('form[action$="/en/preferences"]').first();
    await form.locator('input[type=checkbox][name=notify_milestones]').check();
    await form.locator('input[type=checkbox][name=notify_product]').check();
    await form.locator('[type=submit]').click();
    await expect.poll(() => prefs(email)).toBe('101|0');
    await expect(form.locator('input[type=checkbox][name=notify_milestones]')).toBeChecked();
    await expect(form.locator('input[type=checkbox][name=notify_new_signatures]')).not.toBeChecked();

    const token = db(`select unsubscribe_token from users where email='${email}'`);
    expect(token).not.toBe('');
    await page.context().clearCookies();
    await page.goto(`/en/unsubscribe/${token}`);
    expect(prefs(email)).toBe('101|0');
    await page.locator(`form[action$="/unsubscribe/${token}"] [type=submit]`).click();
    await expect.poll(() => prefs(email)).toBe('000|1');
    await expect(page.getByText('You have been unsubscribed from milestone and product emails.')).toBeVisible();
});

test('an unknown unsubscribe token changes nothing and a guest is sent to login', async ({ page }) => {
    const res = await page.request.get(`/en/unsubscribe/${uniq('bogus')}`);
    expect(res.status()).toBe(200);
    expect(await res.text()).toContain('This unsubscribe link is invalid or has expired.');

    const guest = await page.request.get('/en/preferences', { maxRedirects: 0 });
    expect(guest.status()).toBe(302);
    expect(guest.headers().location).toContain('/login');
});
