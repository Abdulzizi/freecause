import { test, expect, PETITION, adminLogin } from './helpers.js';

for (const [name, path] of [
    ['home', '/en'],
    ['german home', '/de'],
    ['petition list', '/en/petitions'],
    ['petition detail', PETITION],
    ['create petition', '/en/create-petition'],
    ['login', '/en/login'],
    ['register', '/en/register'],
]) {
    test(`${name} renders`, async ({ page }) => {
        const response = await page.goto(path);
        expect(response.status()).toBe(200);
        await expect(page.locator('body')).toBeVisible();
    });
}

test('guest can sign a petition', async ({ page }) => {
    await page.goto('/en/petition/clean-the-river/2');
    const cta = page.locator('[data-sign-cta]:visible').first();
    if (await cta.count()) {
        await cta.click();
        await expect(page).toHaveURL(/\/sign\/confirm/);
    }
    const form = page.locator('form.fc-sign-form');
    await form.locator('[name=name]:visible').first().fill('Ada Tester');
    await form.locator('[name=email]:visible').first().fill('ada@e2e.test');
    for (const n of ['agree1', 'agree2', 'agree3']) await form.locator(`input[type=checkbox][name=${n}]:visible`).first().check();
    await form.locator('[type=submit]:visible').first().click();
    await page.getByRole('button', { name: /^sign/i }).first().click();
    await expect(page).toHaveURL(/\/thanks/);
    await expect(page.locator('h1').first()).toContainText(/thank/i);
});

test('admin can open a petition drawer', async ({ page }) => {
    await adminLogin(page);
    await page.goto('/admin/petitions?select=1&locale=en');
    await expect(page.getByText('Protect the city park').first()).toBeVisible();
});

test('admin sidebar remembers a collapsed group', async ({ page }) => {
    await adminLogin(page);
    await page.evaluate(() => localStorage.setItem('fc-group-system', '1'));
    await page.reload();
    const open = await page.locator('[x-init*="fc-group-system"]').first().evaluate((el) => window.Alpine.$data(el).open);
    expect(open).toBe(false);
});
