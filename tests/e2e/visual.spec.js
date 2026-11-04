import { test, expect, PETITION } from './helpers.js';

for (const width of [375, 1280]) {
    for (const [name, path] of [
        ['components', '/__components'],
        ['home-en', '/en'],
        ['home-de', '/de'],
        ['petition', PETITION],
    ]) {
        test(`${name} at ${width}px`, async ({ page }) => {
            await page.setViewportSize({ width, height: 800 });
            await page.goto(path);
            await expect(page).toHaveScreenshot(`${name}-${width}.png`, { fullPage: true, animations: 'disabled', maxDiffPixelRatio: 0.01 });
        });
    }
}
