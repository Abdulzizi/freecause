# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Full setup (install deps, .env, key, migrate)
composer run setup

# Start all dev services (server + queue + logs + vite)
composer run dev

# Run tests (clears config cache first, uses SQLite in-memory)
composer run test

# Code style (PSR-12 via Laravel Pint)
./vendor/bin/pint

# Create first admin account
php artisan admin:create

# Production optimization
php artisan optimize
php artisan storage:link
```

**Key custom artisan commands:**
- `php artisan signatures:reconcile [--dry-run]` — Sync denormalized `signature_count` vs actual DB count
- `php artisan cache:warm` — Pre-populate petition/category/settings cache (run hourly in prod)
- `php artisan backup:database` — SQL dump to `storage/app/backups/`
- `php artisan images:cleanup` — Remove orphaned petition cover images

## Architecture

### Request Flow

All public routes are prefixed with `/{locale}/` (e.g., `/en/petitions`). The root `/` redirects to the cached default language. Every request passes through:

1. **`BlockBannedIp`** (global, appended) — Checks `banned_ips` table (5-min cache per IP). Aborts 403.
2. **`SetLocale`** — Parses locale from URL segment 1, validates against active languages (60s cache), sets `App::setLocale()`.
3. Admin routes additionally run **`AdminAuth`** → **`AdminAuditLog`** → **`CheckPermission`**.

### External Services Policy

**Only two external services are permitted:**
- **Google OAuth** — via `laravel/socialite`. Credentials (`google_client_id`, `google_client_secret`) stored in the `settings` DB table (`global` group).
- **SMTP — Brevo** — host: `smtp-relay.brevo.com`, port: 587, TLS. Credentials stored in `settings` DB table (`global` group). Keys: `smtp_host`, `smtp_port`, `smtp_user`, `smtp_pass`, `smtp_encryption`, `email_from`. Read by `AppServiceProvider::boot()` via `Settings::get('smtp_host')`.

**Nothing else.** No Cloudinary, Resend, AWS S3, Mailgun, Postmark, Pusher, Sentry, or any other external service. Images are stored locally in `storage/app/public/petitions/` and resized with `intervention/image-laravel`. Backups go to `storage/app/backups/` (local disk).

### Cache & Queue Drivers

Production uses **Redis** (`predis/predis`) for cache, sessions, and queue. Configure in `.env`:
```
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

### Deployment Workflow

**Always use git — never SCP files directly to production.**

```bash
# Local → GitHub → Server
git add <files>
git commit -m "message"
git push origin main
ssh root@37.60.254.112 "cd /var/www/freecause && git pull origin main && php artisan migrate --force && php artisan cache:clear && php artisan config:clear && php artisan view:clear && systemctl restart php8.3-fpm"
```

### Fresh Server Setup

After `git clone` + `composer install` + `.env` setup:
```bash
php artisan migrate --force
php artisan db:seed --class=PageContentSeeder --force   # required — home page content
php artisan cache:warm
php artisan storage:link
php artisan optimize
```

### Dynamic Configuration (Settings Table)

SMTP, Google OAuth credentials, base URL, and ads.txt are stored in the `settings` DB table — not in `.env`. They're read in `AppServiceProvider::boot()` via `Settings::get()` (5-min cache). The `Settings` support class wraps all access with `Cache::remember()`. Use `Settings::set()` to update and auto-bust the cache.

### Translation System (File-based only)

Translations live exclusively in `lang/{locale}/*.php` files — one file per group per locale (e.g., `lang/en/auth.php`, `lang/fr/sign.php`). Laravel's `__('group.key')` maps to `lang/{locale}/group.php` → array key `key`. `config/app.php` sets `fallback_locale = 'en'` so missing keys in non-English locales transparently fall back to the English file.

**No DB-based translation layer.** The old `translations` table (Spatie) and `TranslationService` were removed. The admin translation editor at `/admin/translations` no longer exists.

**Home page content** (`h1`, `h2`, tabs, badges) is stored in the `page_contents` DB table (per-locale, per-page key/value pairs) so admins can edit it without touching files. The `HomeController` loads it via `PageContent` model and caches it 30 min. The `lang/en/home.php` file is used as a fallback via `?? __('home.key')` in the view. If the DB has wrong data, clear it: `DELETE FROM page_contents WHERE page='home';` then re-seed: `php artisan db:seed --class=PageContentSeeder --force`.

### Featured Petitions (Per-Locale)

`petition_translations.is_featured` controls which petitions appear on each locale's home page. A petition featured for Italian only appears on `/it/`, not `/en/`. The `HomeController` pool query filters on `pt_locale.is_featured = 1`. If a locale has no featured petitions it falls back to the default locale's pool.

**Admin workflow:** Open a petition in the admin panel, select the locale, check "featured (locale: xx)" and save. The bulk "feature" action only marks the **default locale** translation as featured. Use the edit panel to feature individually per locale.

**Do not** run `UPDATE petition_translations SET is_featured = 1 WHERE petition_id IN (...)` without scoping to a specific locale — that would broadcast the petition to all locales' home pages.

### Petition & Signature Denormalization

`petitions.signature_count` is denormalized (not computed from `COUNT(*)`). It's incremented via `DB::table('petitions')->increment('signature_count')` after `Signature::firstOrCreate(...)->wasRecentlyCreated`. Reconcile drift with `php artisan signatures:reconcile`. **Admin bulk-delete of signatures does NOT decrement the count** — reconcile after any bulk deletes.

### Multi-Locale Slugs

Each petition has a `petition_translations` row per locale with its own `slug`. Slugs are unique per locale globally. The `lroute()` helper (in `app/helpers.php`) injects the current locale into named routes and also handles slug translation for petition URLs when switching languages.

### Cover Image Fallback Chain

`Petition::coverImageUrl()` resolves in order:
1. HTTP(S) URL in `cover_image` field → return as-is
2. Local path in `cover_image` → storage URL if file exists
3. `image_url` field (legacy, prepended with `petitions/`) → if file exists
4. `public/legacy/images/pic{id % 7 + 1}.jpg` (7 defaults)
5. `public/legacy/images/demo-featured.jpg`

### Admin Permissions

Format for `CheckPermission` middleware: `permission:resource,action` (e.g., `middleware('permission:petitions,edit')`). Permissions are stored in the `permissions` table and cached per-admin session. **No cache invalidation on permission change** — admin must re-login for new permissions to take effect.

### Password Compatibility

`AdminAuthController` handles legacy md5+salt passwords alongside bcrypt. A `try/catch` wraps `Hash::check()` to catch `RuntimeException` from malformed hashes. Users are auto-upgraded to bcrypt on successful login.

### Frontend

Assets use Vite 7 with Tailwind CSS v4 (`@tailwindcss/vite`). Production uses `npm run build` — do not use Vite dev server in production. Admin views use CKEditor 4 loaded from CDN (`ckeditor.js`). The `<textarea>` that CKEditor attaches to **must have `id="content"`** (not just `name="content"`) — `CKEDITOR.replace()` uses `getElementById`.

### View Composers

`AppServiceProvider` registers view composers for the navbar partial. These load `pageContent`, `categories`, and `navbarContent` once per request (cached 6–12 hours). If navbar data is stale after an admin update, clear the relevant cache keys.

### Cache Keys to Know

| Key | TTL | Busted by |
|-----|-----|-----------|
| `setting:{group}:{key}` | 300s | `Settings::set()` |
| `banned_ip:{ip}` | 300s | Admin unban |
| `active_languages` / `default_language` | 60s | Language admin save |
| `petitions:index:{locale}:page:{n}` | 300s | Petition publish/unpublish |
| `categories:list:{locale}` | 43200s | Category admin save |
| `home:content:v2:{locale}` | 1800s | Re-seed PageContentSeeder or manual cache:clear |
| `home:pool:v2:{locale}` | 300s | Petition featured status change |
| `home:featured:v2:{locale}:{slot}` | 65s | Auto-expires (rotates every minute) |
| `approx_rows:{table}` | 3600s | Auto-expires |

### Scale Context

Production DB has ~18M users, 16M+ signatures. Full-table queries without indexes will cause timeouts. Use `ApproxRows` trait (wraps `information_schema.TABLES`) for count estimates in admin list headers. Avoid `whereDate()` — it wraps the column in `DATE()` and breaks index usage; use `whereBetween()` with explicit timestamps instead.
