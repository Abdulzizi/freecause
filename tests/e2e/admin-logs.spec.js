import { test, expect, uniq, db, adminLogin, confirmDialog } from './helpers.js';

const prefix = uniq('E2E log ');
const insertLog = (title, level, createdAt = "datetime('now')") =>
    db(`insert into logs (level, context, title, content, ip, created_at, updated_at) values ('${level}', 'e2e', '${title}', 'e2e body', '198.51.100.7', ${createdAt}, ${createdAt}); select last_insert_rowid();`);
const trashed = (id) => db(`select deleted_at is not null from logs where id=${id}`);

test('admin filters logs, bulk deletes one with undo, prunes old ones and restores them', async ({ page }) => {
    const warn = insertLog(`${prefix} warn`, 'warning');
    const err = insertLog(`${prefix} err`, 'error');
    const old = insertLog(`${prefix} old`, 'info', "datetime('now', '-400 days')");

    await adminLogin(page);
    await page.goto('/admin/logs');
    await page.locator('input[name=q]').fill(prefix);
    await page.locator('input[name=q]').press('Enter');
    await expect(page.locator('tbody input.bulk-checkbox')).toHaveCount(3);
    await page.locator('select[name=level]').selectOption('error');
    await expect(page).toHaveURL(/level=error/);
    await expect(page.locator('tbody input.bulk-checkbox')).toHaveCount(1);
    await expect(page.locator(`tbody input.bulk-checkbox[value="${err}"]`)).toBeAttached();

    await page.goto(`/admin/logs?q=${encodeURIComponent(prefix)}`);
    await page.locator(`tbody input.bulk-checkbox[value="${warn}"]`).check();
    await page.locator('#bulk-delete').click();
    await confirmDialog(page).click();
    await expect.poll(() => trashed(warn)).toBe('1');
    await page.getByRole('button', { name: 'Undo', exact: true }).click();
    await expect.poll(() => trashed(warn)).toBe('0');

    await page.goto('/admin/logs');
    await page.locator('select[name=days]').selectOption('30');
    await page.getByRole('button', { name: 'prune old logs' }).click();
    // The prune redirects back to this page; wait for that reload before clicking anything else.
    const reloaded = page.waitForResponse((r) => r.request().method() === 'GET' && new URL(r.url()).pathname === '/admin/logs');
    await confirmDialog(page).click();
    await reloaded;
    await page.waitForLoadState('load');
    await expect.poll(() => trashed(old)).toBe('1');
    expect(trashed(warn) + trashed(err)).toBe('00');

    await page.getByRole('button', { name: 'undo last delete' }).click();
    await confirmDialog(page).click();
    await expect.poll(() => trashed(old)).toBe('0');
});
