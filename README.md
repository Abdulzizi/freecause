# FreeCause

A multi-language online petition platform built on Laravel 12. Users create, sign, and share petitions; admins manage content, moderation, and site configuration from a full admin panel.

## Stack

| Layer      | Technology                                    |
|------------|-----------------------------------------------|
| Framework  | Laravel 12 (PHP 8.3+)                         |
| Frontend   | Blade + Tailwind CSS v4, Vite                 |
| Database   | MySQL (prod), SQLite (local/test)             |
| Cache/Queue| Redis or database-backed, Supervisor workers  |
| Auth       | Email/password, Google OAuth (Socialite), TOTP 2FA |
| PDF        | barryvdh/laravel-dompdf                       |
| Images     | intervention/image-laravel                    |
| CI/CD      | GitHub Actions (`deploy.yml`, tag-triggered)  |

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
resources/
  lang/                     PHP translation files (14 locales)
  views/                    Blade templates (pages/, admin/, petition/)
routes/web.php              All routes: public, auth, admin
.github/workflows/deploy.yml  Tag-triggered deploy pipeline
freecause-queue.conf        Supervisor worker config
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
php artisan admin:create     # first admin account
php artisan storage:link
npm run build
php artisan serve
```

Local dev server: `http://localhost:8000`.

## Commands

| Command            | What it does                                        |
|--------------------|-----------------------------------------------------|
| `composer dev`     | Dev server, queue worker, log tail, Vite in parallel |
| `composer test`    | Config clear + full test suite                      |
| `php artisan test --filter=X` | Targeted tests                           |
| `./vendor/bin/pint`| PSR-12 style fixes (Laravel Pint)                   |
| `php artisan cache:warm` | Warm versioned petition/settings cache keys   |

Tests run against sqlite in-memory: `CACHE_STORE=array DB_CONNECTION=sqlite DB_DATABASE=:memory:`.

## Configuration

`.env` keys that matter most:

```dotenv
APP_URL=https://www.freecause.com
DB_CONNECTION=mysql
CACHE_STORE=redis
SESSION_DRIVER=database
QUEUE_CONNECTION=database
REDIS_CACHE_DB=1
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI="${APP_URL}/en/oauth/google/callback"
```

Mail (Brevo SMTP) and much of the runtime branding are DB-settings driven and editable from the admin panel under Options, so changing them requires a queue restart rather than a redeploy.

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
- Translation files: `resources/lang/{locale}/`.
- Translated content (petitions, categories, pages) lives in per-locale DB tables managed through the admin panel.
- `hreflang` tags (all locales + `x-default`) are rendered on home and petition pages.

## Production notes

- **Deploys**: push a tag (`v*`) to trigger GitHub Actions; the pipeline checks out the tag on the server, runs migrations, rebuilds caches, restarts the queue, and smoke-tests `/up`. Never edit app code directly on the server.
  ```bash
  git tag v2026.10.03-example
  git push origin v2026.10.03-example
  ```
- **Queue workers**: Supervisor group `freecause-release-queue:*`. After changing mail/DB-driven settings, restart them rather than redeploying.
- **Backups**: daily `backup:run` at 02:00 writes to the S3 `backups` disk with retention cleanup at 02:30. Do not run `backup:clean` manually against that disk.
- **Health check**: `GET /up` must return 200; the deploy pipeline fails otherwise.
- **Caching**: settings, translations, and petition stats are cached with versioned keys; cache is invalidated on sign writes. An edge CDN may still hold stale files after config changes, so purge by URL when needed.

## Security

| Mechanism        | Where                                    |
|------------------|------------------------------------------|
| CSP headers      | `AddSecurityHeaders` middleware          |
| Rate limiting    | Login, register, sign endpoints          |
| IP banning       | `BlockBannedIp` middleware, `banned_ips` |
| User levels/permissions | `CheckPermission` middleware, `user_levels` table |
| Admin audit log  | `AdminAuditLog` middleware               |
| Email verification | Required before signing as a user      |
| 2FA              | Optional TOTP per admin account          |

## License

Open-source under the MIT license (declared in `composer.json`).
