import { test as setup, adminLogin } from './helpers.js';
import { ADMIN_STATE } from './env.js';

setup('admin session', async ({ page }) => {
    await adminLogin(page);
    await page.context().storageState({ path: ADMIN_STATE });
});
