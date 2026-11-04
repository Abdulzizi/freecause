# FreeCause

A multi-language online petition platform built on Laravel 12. Users create, sign, and share petitions; admins manage content, moderation, and site configuration from a full admin panel.

## Stack

| Layer      | Technology                                    |
|------------|-----------------------------------------------|
| Framework  | Laravel 12 (PHP 8.3+)                         |
| Frontend   | Blade + Tailwind CSS v4, Vite                 |
| Database   | MySQL (prod), SQLite (local/test)             |
| Cache/Queue| Redis or database-backed, Supervisor workers  |
| Auth       | Email/password, Google OAuth (Socialite)      |
| PDF        | barryvdh/laravel-dompdf                       |
| Images     | intervention/image-laravel                    |
| CI/CD      | GitHub Actions: `ci.yml` gate → `deploy.yml` (Deployer 8, on push to `main`) |

## Project layout

```
app/
  Http/Controllers/Admin/   Admin panel controllers
  Http/Controllers/         Public controllers (auth, petitions, widgets)
  Http/Middleware/          CSP, locale, IP ban, audit log, permissions
  Models/                   Eloquent models
  Support/                  Cache keys, settings, WP SSO helpers
  helpers.php               Global helpers (lroute(), Settings, Locale)
database/
  migrations/               Schema history
  seeders/                  User levels, languages, page content
lang/                       PHP translation files (14 locales)
resources/
  views/                    Blade templates (pages/, admin/, petition/)
  js/, css/                 Vite entry points and styles
routes/web.php              All routes: public, auth, admin
deploy/                     Deployer recipe, cutover script, supervisor template
.github/workflows/          ci.yml (gate), deploy.yml (Deployer on push to main), release.yml (GitHub Release on tags), i18n.yml
docs/                       Operational and performance docs
```

## Getting started

Requirements: PHP 8.3+, Composer 2, Node 20+, MySQL 8 (or SQLite for local work).

```bash
git clone <repository-url> freecause
cd freecause
composer setup       # install deps, .env, app key, migrate, npm install + build
composer dev         # serve + queue worker + pail + vite, one command
```

Manual steps, if you prefer:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
npm run build
php artisan serve
```

There is no first-admin command; seed `DemoSeeder` locally for a demo admin, or create/manage admins through the panel and `admin:reset-password`.

Local dev server: `http://localhost:8000`.

## Commands

| Command            | What it does                                        |
|--------------------|-----------------------------------------------------|
| `composer dev`     | Dev server, queue worker, log tail, Vite in parallel |
| `composer test`    | Config clear + full test suite                      |
| `php artisan test --filter=X` | Targeted tests                           |
| `./vendor/bin/pint app database tests routes config` | PSR-12 fixes — always pass explicit paths (a bare run reformats the WordPress tree under `public/magazine/`) |
| `php artisan cache:warm` | Warm versioned petition/settings cache keys   |
| `bash scripts/ds-lint.sh` | Design-system + a11y gate (blocking in CI)  |
| `node --test tests/js/*.test.cjs` | JS unit tests (forms/busy handling; runs in CI) |
| `php scripts/lang-audit.php` | Locale key-parity check across all 14 locales |

Tests run against sqlite in-memory: `CACHE_STORE=array DB_CONNECTION=sqlite DB_DATABASE=:memory:`.

## Configuration

`.env` keys that matter most:

```dotenv
APP_URL=https://www.freecause.com
DB_CONNECTION=mysql
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=database
REDIS_CLIENT=phpredis
REDIS_CACHE_DB=1
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI="${APP_URL}/en/oauth/google/callback"
```

Mail (smtp2go SMTP, DB-driven) and much of the runtime branding are DB-settings driven and editable from the admin panel under Options, so changing them requires a queue restart rather than a redeploy.

## Admin panel

Entry at `/admin/login`. Main sections and routes:

| Section        | Route                  |
|----------------|------------------------|
| Dashboard      | `/admin/dashboard`     |
| Petitions      | `/admin/petitions`     |
| Signatures     | `/admin/signatures`    |
| Users          | `/admin/users`         |
| Categories     | `/admin/categories`    |
| Pages          | `/admin/pages`         |
| Spam/IP bans   | `/admin/spam`          |
| Reports        | `/admin/reports`       |
| Stats          | `/admin/stats`         |
| Logs           | `/admin/logs`          |
| Languages      | `/admin/languages`     |
| Global options | `/admin/options/global`|
| Language options | `/admin/options/language` |
| ads.txt        | `/admin/ads`           |
| System (levels, permissions) | `/admin/system/*` |

Access is gated by `permission:` middleware (view/edit/delete per section) plus system-level checks; every admin action is written to the audit log visible under Logs.

## Internationalization

14 locales: `da de el en es fr it nl pl pt ro ru sv tr`.

- Public routes are locale-prefixed (`/{locale}/petitions`); the root redirects to the browser's locale.
- Translation files: `lang/{locale}/`.
- Translated content (petitions, categories, pages) lives in per-locale DB tables managed through the admin panel.
- `hreflang` tags (all locales + `x-default`) are rendered on home and petition pages.

## Production notes

- **Deploys**: every push to `main` deploys production, so a merge is a release. `ci.yml` runs the gate (`test`, `assets`, `e2e`, `security-audit`), then `deploy.yml` runs **Deployer 8** under the `production` GitHub Environment: it creates a new release under `/var/www/freecause/releases/`, runs migrations, warms caches, restarts queue workers, reloads PHP-FPM, flips the `current` symlink, and smoke-tests `/version`, rolling back on failure. Never edit app code on the server.
- **Versioning**: a `v*` tag only publishes a GitHub Release, with notes from the matching `CHANGELOG.md` section. Tags never deploy.
  ```bash
  git tag -a v$(date +%Y.%m.%d-%H%M%S) -m "..." && git push origin v...
  ```
  Remote commands run with Deployer's `default_timeout`, set to **900s** in
  `deploy/deploy.php` (default 300) — migrations on the 18M-row `users` table
  blow past 300 and a kill mid-`migrate` auto-rolls back while the migrations
  stay committed (see `docs/operations.md`).
- **Rollback**: run the **Deploy** workflow with `action=rollback`, or `dep -f deploy/deploy.php rollback production`. Full runbook in `deploy/README.md`.
- **Queue workers**: Supervisor group `freecause-release-queue:*`, command pointing at `/var/www/freecause/current`. After changing mail/DB-driven settings, restart them rather than redeploying.
- **Backups**: daily `backup:run` at 02:00 writes to the on-host `backups` disk (database only, 7 archives kept), `backup:clean` runs at 04:00 and `backup:monitor` at 06:00. There is no off-host copy; see `docs/operations.md`.
- **Scheduled jobs** (`routes/console.php`, run by `schedule:work` under Supervisor): `signatures:reconcile` 03:00, `cache:warm` hourly, `emails:petition-new-signatures` hourly, `petition:send-digest` daily 09:00 (owners with `verified` + `notify_product`), plus `queue:monitor` and log pruning.
- **Health/version**: `GET /up` must return 200; `GET /version` reports the deployed commit (`app:stamp-version`). The deploy smoke test compares it to the pushed commit.
- **Caching**: settings, translations, and petition stats are cached with versioned keys; cache is invalidated on sign writes. An edge CDN may still hold stale files after config changes, so purge by URL when needed.

## Documentation

- `deploy/README.md` — deploy/rollback runbook, Deployer recipe, shared dirs, Environment setup.
- `docs/operations.md` — server facts, operational traps (incl. the 900s deploy timeout), parked backlog.
- `docs/design-system.md` — tokens, layers, components, and the `ds-lint` gate rules.
- `docs/ux-states.md` — public loading/empty/error/success state matrix and a11y gate rules.
- `docs/performance-edge-cache.md` — edge-cache design and why listings bypass.

## Security

| Mechanism        | Where                                    |
|------------------|------------------------------------------|
| CSP headers      | `AddSecurityHeaders` middleware          |
| Rate limiting    | Login, register, sign endpoints          |
| IP banning       | `BlockBannedIp` middleware, `banned_ips` |
| User levels/permissions | `CheckPermission` middleware, `user_levels` table |
| Admin audit log  | `AdminAuditLog` middleware               |
| Email verification | Admin setting `require_email_verification`; signed-in users must be verified to sign |

## License

Open-source under the MIT license (declared in `composer.json`).
