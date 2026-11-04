import { test, expect, uniq, db } from './helpers.js';

const spamCount = () => Number(db("select count(*) from spam_logs where type='contact'"));

test('a visitor sends the contact form and sees the confirmation', async ({ page }) => {
    const before = spamCount();
    await page.goto('/en/contacts');
    await page.locator('#contact_name').fill('Carla Contact');
    await page.locator('#contact_email').fill(`${uniq('contact')}@e2e.test`);
    await page.locator('#contact_text').fill('Hello, this is an end-to-end test message about a petition.');
    await page.locator('#contactForm [type=submit]').click();
    await expect(page.locator('#contactSuccess')).toBeVisible();
    await expect(page.locator('#contactForm')).toBeHidden();
    expect(spamCount()).toBe(before);
});

test('a spam message is logged and not accepted', async ({ page }) => {
    const before = spamCount();
    await page.goto('/en/contacts');
    const token = await page.locator('#contactForm input[name=_token]').inputValue();
    const res = await page.request.post('/en/contacts', {
        form: { _token: token, name: 'Spammer', email: `${uniq('spam')}@e2e.test`, text: 'free money for everyone' },
        maxRedirects: 0,
    });
    expect(res.status()).toBe(302);
    expect(spamCount()).toBe(before + 1);
    await page.goto('/en/contacts');
    await expect(page.locator('#contactForm')).toBeVisible();
});
