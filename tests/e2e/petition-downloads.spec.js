import { test, expect, uniq, newUser, createPetition, guestSign, setRequireVerification } from './helpers.js';

test.describe.configure({ mode: 'serial' });

let petition;
let ownerCookies;
const signer = { name: 'Dora Downloader', email: `${uniq('dl')}@e2e.test` };

test.beforeAll(async ({ browser }) => {
    const page = await browser.newPage();
    await setRequireVerification(page, false);
    await page.close();

    const owner = await browser.newPage();
    await newUser(owner, 'dlown');
    petition = await createPetition(owner, uniq('E2E Download '));
    ownerCookies = await owner.context().cookies();
    await owner.close();

    const guest = await browser.newPage();
    await guestSign(guest, petition, signer);
    await guest.close();
});

test('the owner sees the export links and downloads TXT, CSV and PDF that include the signer', async ({ page }) => {
    await page.context().addCookies(ownerCookies);
    await page.goto(petition);
    await expect(page.getByRole('link', { name: 'TXT', exact: true })).toBeVisible();
    await expect(page.getByRole('link', { name: 'PDF', exact: true })).toBeVisible();

    const txt = await page.request.get(`${petition}/download/txt`);
    expect(txt.status()).toBe(200);
    expect(txt.headers()['content-type']).toContain('text/plain');
    expect(await txt.text()).toContain(signer.name);

    const csv = await page.request.get(`${petition}/download/csv`);
    expect(csv.status()).toBe(200);
    const body = await csv.text();
    expect(body).toContain(signer.name);
    // Owners get no email column; only admins do.
    expect(body.split('\n')[0]).toBe('\uFEFF#,name,comment,locale,signed_at');
    expect(body).not.toContain(signer.email);

    const pdf = await page.request.get(`${petition}/download/pdf`);
    expect(pdf.status()).toBe(200);
    expect(pdf.headers()['content-type']).toContain('application/pdf');
    expect((await pdf.body()).subarray(0, 4).toString()).toBe('%PDF');
});

test('a stranger gets 403 and a guest is sent to login for every export', async ({ page, browser }) => {
    await newUser(page, 'dlstranger');
    const guest = await browser.newContext();
    for (const format of ['txt', 'csv', 'pdf']) {
        expect((await page.request.get(`${petition}/download/${format}`)).status()).toBe(403);
        const anon = await guest.request.get(`${petition}/download/${format}`, { maxRedirects: 0 });
        expect(anon.status()).toBe(302);
        expect(anon.headers().location).toContain('/login');
    }
    await guest.close();
});
