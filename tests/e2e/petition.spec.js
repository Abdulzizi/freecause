import { test, expect, uniq, db, newUser, createPetition, guestSign, setRequireVerification } from './helpers.js';

test.describe.configure({ mode: 'serial' });

test.beforeAll(async ({ browser }) => {
    const page = await browser.newPage();
    await setRequireVerification(page, true);
    await page.close();
});

test.afterAll(async ({ browser }) => {
    const page = await browser.newPage();
    await setRequireVerification(page, false);
    await page.close();
});

test('owner creates a petition and it is published with one signature', async ({ page }) => {
    await newUser(page, 'own');
    const title = uniq('E2E Create ');
    await createPetition(page, title);
    await expect(page).toHaveURL(/\/thanks/);
    expect(db(`select status||'|'||signature_count from petitions p join petition_translations t on t.petition_id=p.id where t.title='${title}'`)).toBe('published|1');
});

test('owner edits own petition, stranger cannot, owner locked after another signer', async ({ page, browser }) => {
    await newUser(page, 'edit');
    const title = uniq('E2E Edit ');
    const path = await createPetition(page, title);
    const id = path.split('/').pop();

    await page.goto(`${path}/edit`);
    const newTitle = `${title} renamed`;
    await page.locator('input[name=title]').fill(newTitle);
    await page.locator('form[data-petition-form] [type=submit]:visible').first().click();
    await expect.poll(() => db(`select title from petition_translations where petition_id=${id}`)).toBe(newTitle);

    const other = await browser.newPage();
    await newUser(other, 'stranger');
    expect((await other.request.get(`${path}/edit`)).status()).toBe(403);
    await other.close();

    const guest = await browser.newPage();
    await guestSign(guest, path, { name: 'Late Signer', email: `${uniq('late')}@e2e.test` });
    await guest.close();
    expect((await page.request.get(`${path}/edit`)).status()).toBe(403);
});

test('public 403 page renders one clean document', async ({ page, browser }) => {
    await newUser(page, 'own403');
    const path = await createPetition(page, uniq('E2E 403 '));
    const other = await browser.newPage();
    await newUser(other, 'str403');
    const html = await (await other.request.get(`${path}/edit`)).text();
    expect(html.match(/<html/g)).toHaveLength(1);
    await other.goto(`${path}/edit`);
    await expect(other.locator('h1').first()).toBeVisible();
    await other.close();
});
