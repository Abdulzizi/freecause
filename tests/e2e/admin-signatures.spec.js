import { test, expect, uniq, db, newUser, createPetition, guestSign, petitionRow, adminLogin, setRequireVerification, confirmDialog } from './helpers.js';

test.describe.configure({ mode: 'serial' });

let petition;
let email;

test.beforeAll(async ({ browser }) => {
    const page = await browser.newPage();
    await setRequireVerification(page, true);
    await newUser(page, 'sigown');
    petition = await createPetition(page, uniq('E2E Signatures '));
    email = `${uniq('sig')}@e2e.test`;
    await page.close();
    const guest = await browser.newPage();
    await guestSign(guest, petition, { name: 'Pending Person', email });
    await guest.close();
});

test.afterAll(async ({ browser }) => {
    const page = await browser.newPage();
    await setRequireVerification(page, false);
    await page.close();
});

async function selectRow(page, action) {
    await page.goto(`/admin/signatures?email=${encodeURIComponent(email)}&confirmed=&spam=`);
    await page.locator('tbody input[type=checkbox]').first().check();
    await page.locator(`.bulk-action[data-action=${action}]`).click();
    await confirmDialog(page).click();
}

test('admin approves a pending signature, which then counts', async ({ page }) => {
    expect(db(`select confirmed from signatures where email='${email}'`)).toBe('0');
    expect(petitionRow(petition)).toBe('1');
    await adminLogin(page);
    await selectRow(page, 'approve');
    await expect.poll(() => db(`select confirmed from signatures where email='${email}'`)).toBe('1');
    expect(petitionRow(petition)).toBe('2');
});

test('admin rejects the signature, which stops counting', async ({ page }) => {
    await adminLogin(page);
    await selectRow(page, 'reject');
    await expect.poll(() => petitionRow(petition)).toBe('1');
});

test('admin deletes the signature', async ({ page }) => {
    await adminLogin(page);
    await selectRow(page, 'delete');
    await expect.poll(() => db(`select count(*) from signatures where email='${email}'`)).toBe('0');
});
