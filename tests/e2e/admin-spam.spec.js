import { test, expect, db, adminLogin, confirmDialog } from './helpers.js';

// TEST-NET-3 address: never the 127.0.0.1 / ::1 the test server and browser run on.
const IP = '203.0.113.77';
const REVIEW_IP = '198.51.100.10';
const banned = () => db(`select count(*) from banned_ips where ip='${IP}'`);

// Ban and unban post a form that redirects back here; wait for that reload before clicking anything else.
async function confirmAndReload(page) {
    const reloaded = page.waitForResponse((r) => r.request().method() === 'GET' && new URL(r.url()).pathname === '/admin/spam');
    await confirmDialog(page).click();
    await reloaded;
    await page.waitForLoadState('load');
}

test.afterAll(() => {
    db(`delete from banned_ips where ip='${IP}'`);
    db(`delete from spam_logs where type='petition.review' and ip='${REVIEW_IP}'`);
});

test('admin filters spam logs, bans and unbans an IP, then clears the logs', async ({ page }) => {
    db(`insert into spam_logs (type, ip, payload, created_at) values ('e2e-spam', '${IP}', '{"text":"e2e"}', datetime('now'))`);
    db("insert into spam_logs (type, ip, payload, created_at) values ('e2e-other', '198.51.100.9', '{}', datetime('now'))");
    db(`insert into spam_logs (type, ip, payload, created_at) values ('petition.review', '${REVIEW_IP}', '{}', datetime('now'))`);

    await adminLogin(page);
    await page.goto('/admin/spam');
    await page.locator('select[name=type]').selectOption('e2e-spam');
    await expect(page).toHaveURL(/type=e2e-spam/);
    const row = page.locator('tbody tr', { hasText: IP });
    await expect(row).toHaveCount(1);
    await expect(page.locator('tbody tr', { hasText: '198.51.100.9' })).toHaveCount(0);

    await row.getByRole('button', { name: 'Row actions' }).click();
    await page.getByRole('menuitem', { name: 'Ban IP' }).click();
    await confirmAndReload(page);
    await expect.poll(banned).toBe('1');

    await page.locator('tbody tr', { hasText: IP }).getByRole('button', { name: 'Row actions' }).click();
    await page.getByRole('menuitem', { name: 'Unban IP' }).click();
    await confirmAndReload(page);
    await expect.poll(banned).toBe('0');

    await page.getByRole('button', { name: 'clear spam logs' }).click();
    await confirmDialog(page).click();
    await expect.poll(() => db("select count(*) from spam_logs where type != 'petition.review'")).toBe('0');
    expect(db(`select count(*) from spam_logs where type='petition.review' and ip='${REVIEW_IP}'`)).toBe('1');
});
