import { test, expect, uniq, db, newUser, login, expectLoginRefused, PASSWORD } from './helpers.js';
import { execFileSync } from 'node:child_process';
import fs from 'node:fs';

test.describe.configure({ mode: 'serial' });

const PHP = fs.existsSync('/opt/homebrew/opt/php/bin/php') ? '/opt/homebrew/opt/php/bin/php' : 'php';
const NEW_PASSWORD = 'e2e-Reset-Password-2';

// The broker stores only a bcrypt hash of the mailed token and the e2e mailer is in-memory, so the plaintext is
// unreachable. After the real request we swap the stored hash for one of a token we know (PHP's bcrypt is what Hash::check reads).
function plantToken(email) {
    const token = uniq('tok');
    const hash = execFileSync(PHP, ['-r', 'echo password_hash($argv[1], PASSWORD_BCRYPT);', token], { encoding: 'utf8' });
    db(`update password_reset_tokens set token='${hash}' where email='${email}'`);
    return token;
}

async function requestLink(page, email) {
    await page.goto('/en/forgot-password');
    await page.locator('#forgot_email').fill(email);
    await page.locator('form[action$="/en/forgot-password"] [type=submit]').click();
    await expect.poll(() => db(`select count(*) from password_reset_tokens where email='${email}'`)).toBe('1');
}

async function submitReset(page, token, email) {
    await page.goto(`/en/reset-password/${token}?email=${encodeURIComponent(email)}`);
    await expect(page.locator('#reset_email')).toHaveValue(email);
    await page.locator('#reset_password').fill(NEW_PASSWORD);
    await page.locator('input[name=password_confirmation]').fill(NEW_PASSWORD);
    await page.locator('form[action$="/en/reset-password"] [type=submit]').click();
}

test('forgot password stores a hashed token, the link resets the password, and only the new one logs in', async ({ page }) => {
    const email = await newUser(page, 'reset');
    await page.context().clearCookies();

    await requestLink(page, email);
    expect(db(`select substr(token, 1, 4) from password_reset_tokens where email='${email}'`)).toBe('$2y$');

    await submitReset(page, plantToken(email), email);
    await expect(page).not.toHaveURL(/reset-password|login/);
    expect(db(`select count(*) from password_reset_tokens where email='${email}'`)).toBe('0');
    expect(db(`select password_changed_at is not null from users where email='${email}'`)).toBe('1');

    await page.context().clearCookies();
    await expectLoginRefused(page, email, PASSWORD);
    await page.context().clearCookies();
    await login(page, email, NEW_PASSWORD);
    await expect(page).not.toHaveURL(/\/login/);
    await page.goto('/en/profile');
    await expect(page).toHaveURL(/\/en\/profile/);
});

test('an unknown token and an expired token are both refused and the password is kept', async ({ page }) => {
    const email = await newUser(page, 'resetbad');
    await page.context().clearCookies();
    const hash = () => db(`select password from users where email='${email}'`);
    const before = hash();

    await requestLink(page, email);
    await submitReset(page, 'not-a-real-token', email);
    await expect(page).toHaveURL(/\/reset-password\//);
    await expect(page.locator('#reset_email[aria-invalid=true]')).toBeVisible();

    const token = plantToken(email);
    db(`update password_reset_tokens set created_at=datetime('now', '-2 hours') where email='${email}'`);
    await submitReset(page, token, email);
    await expect(page).toHaveURL(/\/reset-password\//);
    await expect(page.locator('#reset_email[aria-invalid=true]')).toBeVisible();
    expect(hash()).toBe(before);
});
