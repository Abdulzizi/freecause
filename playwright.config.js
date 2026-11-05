import { defineConfig } from '@playwright/test';
import { E2E_DB, E2E_ORIGIN, E2E_PORT } from './tests/e2e/env.js';

const visualIgnore = process.env.E2E_VISUAL ? [] : ['**/visual.spec.js'];
const onlyA11y = process.env.E2E_SET === 'a11y';
const withoutA11y = process.env.E2E_SET === 'rest';

export default defineConfig({
    testDir: 'tests/e2e',
    fullyParallel: false,
    workers: 1,
    forbidOnly: !!process.env.CI,
    retries: process.env.CI ? 1 : 0,
    reporter: process.env.CI ? 'github' : 'list',
    snapshotPathTemplate: '{snapshotDir}/{testFileDir}/{testFileName}-snapshots/{arg}{-snapshotSuffix}{ext}',
    projects: [
        { name: 'setup', testMatch: /auth\.setup\.js/ },
        {
            name: 'e2e',
            dependencies: ['setup'],
            testMatch: onlyA11y ? /a11y\.spec\.js$/ : /.*\.spec\.js$/,
            testIgnore: withoutA11y ? [...visualIgnore, '**/a11y.spec.js'] : visualIgnore,
        },
    ],
    use: { baseURL: E2E_ORIGIN, contextOptions: { reducedMotion: 'reduce' }, trace: 'retain-on-failure' },
    webServer: {
        command: 'bash scripts/e2e-serve.sh',
        url: `${E2E_ORIGIN}/en`,
        env: { E2E_DB, E2E_PORT },
        reuseExistingServer: false,
        timeout: 180000,
        stderr: 'pipe',
    },
});
