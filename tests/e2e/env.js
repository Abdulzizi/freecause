import os from 'node:os';
import path from 'node:path';

export const E2E_DB = process.env.E2E_DB || path.join(os.tmpdir(), 'freecause-e2e', 'e2e.sqlite');
export const ADMIN_STATE = path.join(path.dirname(E2E_DB), 'admin-state.json');
