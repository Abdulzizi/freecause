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
- `php artisan translations:audit` — Check translation file vs DB consistency
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
- **Google OAuth** — via `laravel/socialite`. Credentials (`google_client_id`, `google_client_secret`) stored in the `settings` DB table.
- **SMTP — Brevo** — host: `smtp-relay.brevo.com`, port: 587, TLS. Credentials stored in the `settings` DB table AND `.env` as fallback.

**Nothing else.** No Cloudinary, Resend, AWS S3, Mailgun, Postmark, Pusher, Sentry, or any other external service. Images are stored locally in `storage/app/public/petitions/` and resized with `intervention/image-laravel`. Backups go to `storage/app/backups/` (local disk).

### Dynamic Configuration (Settings Table)

SMTP, Google OAuth credentials, base URL, and ads.txt are stored in the `settings` DB table — not in `.env`. They're read in `AppServiceProvider::boot()` via `Settings::get()` (5-min cache). The `Settings` support class wraps all access with `Cache::remember()`. Use `Settings::set()` to update and auto-bust the cache.

### Translation System (Dual Source)

Two sources are merged by `TranslationService`:
1. **File-based**: `lang/{locale}/*.php` arrays
2. **DB-based**: `translations` table (Spatie `language_lines`)

DB entries override file entries for the same key. The admin translation editor at `/admin/translations` writes to the DB. Cache key: `translations:{locale}:{group}` (TTL varies). Cache is invalidated via admin panel — the hardcoded group list in `TranslationService` must be updated if new groups are added.

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
| `translations:{locale}:{group}` | varies | Admin translation save |
| `petitions:index:{locale}:page:{n}` | 300s | Petition publish/unpublish |
| `categories:list:{locale}` | 43200s | Category admin save |
| `approx_rows:{table}` | 3600s | Auto-expires |

### Scale Context

Production DB has ~18M users, 16M+ signatures. Full-table queries without indexes will cause timeouts. Use `ApproxRows` trait (wraps `information_schema.TABLES`) for count estimates in admin list headers. Avoid `whereDate()` — it wraps the column in `DATE()` and breaks index usage; use `whereBetween()` with explicit timestamps instead.
