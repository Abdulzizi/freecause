import { test, expect, uniq, db, newUser, createPetition, setRequireVerification } from './helpers.js';

test.describe.configure({ mode: 'serial' });

let petition;

test.beforeAll(async ({ browser }) => {
    const page = await browser.newPage();
    await setRequireVerification(page, false);
    await newUser(page, 'shoutown');
    petition = await createPetition(page, uniq('E2E Shout '));
    await page.close();
});

// The shoutbox endpoint has no form of its own; take the CSRF token from any session page.
async function postShout(page, comment) {
    await page.goto('/en/contacts');
    const token = await page.locator('#contactForm input[name=_token]').first().inputValue();
    return page.request.post(`${petition}/shoutbox`, { form: { _token: token, comment }, maxRedirects: 0 });
}

const comments = (page) => page.locator('#petition-comments');

test('a signer comments while signing, then updates the comment through the shoutbox', async ({ page }) => {
    const email = await newUser(page, 'shouter');
    const first = uniq('Count me in ');
    await page.goto(petition);
    await page.locator('[data-sign-cta]:visible').first().click();
    const form = page.locator('form[data-sign-busy]').first();
    await form.locator('input[name=comment]:visible').first().fill(first);
    for (const n of ['agree1', 'agree2', 'agree3']) await form.locator(`input[name=${n}]:visible`).first().check();
    await form.locator('[type=submit]:visible').first().click();
    await page.getByRole('button', { name: /^sign/i }).first().click();
    await expect(page).toHaveURL(/\/thanks/);
    await page.goto(petition);
    await expect(comments(page)).toContainText(first);

    const second = uniq('Updated shout ');
    const res = await postShout(page, second);
    expect(res.status()).toBe(302);
    expect(res.headers().location).toContain('#petition-comments');
    expect(db(`select text from signatures where email='${email}'`)).toBe(second);
    await page.goto(petition);
    await expect(comments(page)).toContainText(second);
    await expect(comments(page)).not.toContainText(first);
});

test('a guest is sent to login and a user who has not signed is refused', async ({ page }) => {
    const text = uniq('Guest shout ');
    const guest = await postShout(page, text);
    expect(guest.status()).toBe(302);
    expect(guest.headers().location).toContain('/login');

    await newUser(page, 'shoutnosign');
    expect((await postShout(page, text)).status()).toBe(403);
    expect(db(`select count(*) from signatures where text='${text}'`)).toBe('0');
});
