import { test, expect, uniq, db, newUser } from './helpers.js';
import fs from 'node:fs';
import path from 'node:path';

const STORAGE = path.resolve('storage/app/public');
const COVER = /^petitions\/\w+\.png$/;

test.afterAll(() => {
    // The upload lands in the checkout's real public disk; remove it and its generated variants, even after a failure.
    const covers = db("select p.cover_image from petitions p join petition_translations t on t.petition_id=p.id where t.title like 'E2E Media %' and p.cover_image is not null");
    for (const cover of covers.split('\n').filter((c) => COVER.test(c))) {
        const dir = path.join(STORAGE, path.dirname(cover));
        const stem = path.basename(cover, path.extname(cover));
        for (const f of fs.readdirSync(dir)) if (f.startsWith(stem)) fs.rmSync(path.join(dir, f));
    }
});

test('a petition with an uploaded image, tags, a YouTube link and a custom goal stores and shows them', async ({ page }) => {
    await newUser(page, 'media');
    const title = uniq('E2E Media ');
    await page.goto('/en/create-petition');
    await page.locator('input[name=title]').fill(title);
    await page.locator('#petition_editor').click();
    await page.keyboard.type('E2E petition body with media. '.repeat(8));
    await page.locator('select[name=goal_signatures]').selectOption('5000');
    await page.locator('select[name=category_id]').selectOption({ index: 1 });
    await page.locator('#field-tags').fill(' parks, clean air ,bikes');
    await page.locator('#petition-image').setInputFiles('tests/e2e/fixtures/cover.png');
    await page.locator('#field-youtube').fill('https://youtu.be/dQw4w9WgXcQ?si=abc');
    await page.locator('form[data-petition-form] [type=submit]:visible').first().click();
    await expect(page).toHaveURL(/\/thanks/);

    const petitionPath = new URL(page.url()).pathname.replace(/\/thanks$/, '');
    const id = petitionPath.split('/').pop();
    const row = db(`select goal_signatures||'|'||tags||'|'||youtube_url||'|'||coalesce(image_url,'') from petitions where id=${id}`);
    expect(row).toBe('5000|parks,clean air,bikes|dQw4w9WgXcQ|');
    const cover = db(`select cover_image from petitions where id=${id}`);
    expect(cover).toMatch(COVER);
    expect(fs.existsSync(path.join(STORAGE, cover))).toBe(true);
    for (const w of ['400w', '800w']) expect(fs.existsSync(path.join(STORAGE, cover.replace(/\.png$/, `_${w}.webp`)))).toBe(true);

    await page.goto(petitionPath);
    for (const tag of ['parks', 'clean air', 'bikes']) await expect(page.locator('.badge', { hasText: tag })).toBeVisible();
    await expect(page.locator('[data-abbr-value="5000"]').first()).toBeAttached();
    const img = page.locator('img[data-blurup]').first();
    await expect(img).toHaveAttribute('src', new RegExp(path.basename(cover).replace('.', '\\.') + '$'));
    await expect(page.locator('picture source[type="image/webp"]').first()).toHaveAttribute('srcset', /_400w\.webp 400w, .*_800w\.webp 800w/);
    await expect.poll(() => img.evaluate((el) => el.complete && el.naturalWidth)).toBeGreaterThan(0);
    await expect(page.locator('iframe[src="https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ"]')).toBeAttached();
});
