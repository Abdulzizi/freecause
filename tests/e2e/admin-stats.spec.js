import { test, expect, uniq, db, newUser, createPetition, adminLogin } from './helpers.js';

test('stats page shows the summary and a statistic table built from this run', async ({ page }) => {
    // Creating a petition adds the owner's own signature, so every statistic has a row from this test.
    await newUser(page, 'stats');
    const id = (await createPetition(page, uniq('E2E Stats '))).split('/').pop();
    await adminLogin(page);
    await page.goto('/admin/stats');
    const signatures = Number(db('select count(*) from signatures'));
    const block = page.locator('.flex-wrap.gap-8 > div').filter({ hasText: 'signatures' });
    await expect(block.locator('h2')).toHaveText(signatures.toLocaleString('en-US'));

    await page.locator('select[name=type]').selectOption('petitions_all');
    await expect(page).toHaveURL(/type=petitions_all/);
    await expect(page.locator('tbody tr td:first-child', { hasText: new RegExp(`^${id}$`) })).toBeVisible();

    await page.locator('select[name=type]').selectOption('top_signers');
    await expect(page.locator('thead th', { hasText: 'Total Signatures' })).toBeVisible();
    await expect(page.locator('tbody tr').first()).toBeVisible();

    expect((await page.request.get('/admin/stats?type=not-a-stat')).status()).toBe(200);
});
