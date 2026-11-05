import os from 'node:os';
import path from 'node:path';

export const E2E_DB = process.env.E2E_DB || path.join(os.tmpdir(), 'freecause-e2e', 'e2e.sqlite');
export const ADMIN_STATE = path.join(path.dirname(E2E_DB), 'admin-state.json');
export const E2E_PORT = process.env.E2E_PORT || '8099';
export const E2E_ORIGIN = `http://127.0.0.1:${E2E_PORT}`;

