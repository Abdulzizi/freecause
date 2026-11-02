import { test, expect, uniq, db, newUser, createPetition, adminLogin, setRequireVerification, confirmDialog } from './helpers.js';

test.describe.configure({ mode: 'serial' });

let held;
let reported;

test.beforeAll(async ({ browser }) => {
    const page = await browser.newPage();
    await setRequireVerification(page, false);
    await page.close();
});

test('a petition with a review keyword is held as a draft, hidden, then published by an admin', async ({ page, browser }) => {
    await newUser(page, 'held');
    held = await createPetition(page, uniq('E2E fortune teller ban '));
    const id = held.split('/').pop();
    expect(db(`select status from petitions where id=${id}`)).toBe('draft');

    const visitor = await browser.newPage();
    expect((await visitor.request.get(held)).status()).toBe(404);

    const admin = await browser.newPage();
    await adminLogin(admin);
    await admin.goto(`/admin/petitions?id=${id}`);
    await admin.locator('tbody input[type=checkbox]').first().check();
    await admin.locator('.bulk-action[data-action=publish]').click();
    await confirmDialog(admin).click();
    await expect.poll(() => db(`select status from petitions where id=${id}`)).toBe('published');
    expect((await visitor.request.get(held)).status()).toBe(200);
    await visitor.close();
    await admin.close();
});

test('a user reports a petition and an admin resolves the report', async ({ page, browser }) => {
    await newUser(page, 'rep');
    reported = await createPetition(page, uniq('E2E Reported '));
    const id = reported.split('/').pop();

    const reporter = await browser.newPage();
    await newUser(reporter, 'reporter');
    await reporter.goto(reported);
    await reporter.locator('[data-sheet-open=reportSheet]:visible').first().click();
    await reporter.locator('#reportSheet form button[type=submit]').click();
    await expect.poll(() => db(`select count(*) from reports where reportable_id=${id}`)).toBe('1');
    await reporter.close();

    const reportId = db(`select id from reports where reportable_id=${id}`);
    const admin = await browser.newPage();
    await adminLogin(admin);
    await admin.goto(`/admin/reports?select=${reportId}`);
    const form = admin.locator('form[action$="/admin/reports/status"]');
    await form.locator('select[name=status]').selectOption('resolved');
    await form.getByRole('button', { name: /^save/i }).click();
    await expect.poll(() => db(`select status from reports where id=${reportId}`)).toBe('resolved');
    await admin.close();
});
