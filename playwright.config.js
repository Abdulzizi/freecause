import { defineConfig } from '@playwright/test';

export default defineConfig({
    testDir: 'tests/e2e',
    fullyParallel: false,
    workers: 1,
    forbidOnly: !!process.env.CI,
    retries: 0,
    reporter: process.env.CI ? 'github' : 'list',
    testIgnore: process.env.E2E_VISUAL ? [] : ['**/visual.spec.js'],
    use: { baseURL: 'http://127.0.0.1:8099', contextOptions: { reducedMotion: 'reduce' }, trace: 'retain-on-failure' },
    webServer: {
        command: 'bash scripts/e2e-serve.sh',
        url: 'http://127.0.0.1:8099/en',
        reuseExistingServer: false,
        timeout: 180000,
        stderr: 'pipe',
    },
});
