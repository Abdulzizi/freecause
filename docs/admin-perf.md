# Admin Panel Performance Baseline (Phase 0)

Purpose: establish a local, repeatable baseline for admin panel GET pages before
optimization work starts. The baseline measures **query counts** (meaningful) and
**response time in ms** (indicative only) for the main admin index/detail pages.

## Methodology

Temporary feature test (`TempAdminPerfProbeTest`, deleted after the run):

- `RefreshDatabase`, seeders `UserLevelSeeder` + `LanguageSeeder`, admin user
  (`User::factory()` with `level_id` = seeded `admin` level, `verified = true`),
  `actingAs($admin, 'admin')`, `withoutVite()`.
- Per URI: `DB::flushQueryLog()` → `DB::enableQueryLog()` → `microtime(true)` →
  `$this->get($uri)` → ms → `count(DB::getQueryLog())` → `assertOk()`.
- Command:
  `CACHE_STORE=array DB_CONNECTION=sqlite DB_DATABASE=:memory: /opt/homebrew/opt/php/bin/php artisan test --filter=TempAdminPerfProbe`

### Caveats

- **sqlite in-memory test env**: wall-clock ms reflect test-env rendering, not
  production. Treat timings as *indicative* and query counts as the real signal.
- No cache warm-up between pages; each page measured cold in the same request
  context (migrations already done by RefreshDatabase).
- Every page rendered with a single admin user and near-empty content tables.
  Query counts on real data (large list pages) may be higher if per-row queries
  exist; this baseline catches N+1 only for the seeded/empty case.
- Blade render time included; js/css excluded (`withoutVite()`).

## Baseline (local, Oct 2026)

| Page | Queries | ms |
|---|---:|---:|
| `/admin/dashboard` | 22 | 131.08 |
| `/admin/users` | 5 | 69.36 |
| `/admin/petitions` | 4 | 72.18 |
| `/admin/categories` | 4 | 38.63 |
| `/admin/pages` | 5 | 53.31 |
| `/admin/signatures` | 8 | 28.40 |
| `/admin/spam` | 5 | 23.88 |
| `/admin/logs` | 2 | 22.60 |
| `/admin/stats` | 6 | 19.77 |
| `/admin/options/global` | 2 | 30.50 |
| `/admin/languages` | 2 | 38.35 |
| `/admin/system/user-info` | 1 | 20.49 |
| `/admin/system/user-levels` | 4 | 23.03 |
| `/admin/system/permissions` | 3 | 19.42 |

Outlier: **dashboard at 22 queries** — first target for query reduction
(inspect dashboard controller aggregates / repeated Settings lookups).

## Full-navigation flows (code inspection)

Admin interactions are mostly full-page navigations; each is a fresh render +
query round trip:

- **Filters autosubmit** — `data-admin-autosubmit` selects submit the parent form
  immediately (`resources/js/admin/core.js:120` → `form.requestSubmit()`), i.e. a
  full GET navigation per filter change.
- **Drawer opens/closes by navigating** — row click / close set
  `window.location.href` to the list URL with `?select=ID` / without it
  (`resources/js/admin/drawer.js:195,218`), reloading the whole page to show or
  hide the detail sheet.
- **Saves redirect + reload** — form posts are PRG; the redirect back to the
  list URL (preserving `?select=ID&locale=XX`) re-renders the page.
- **Bulk / signature actions fetch then reload** — JSON POST via `fetch`, then
  `window.location.reload()` (e.g. `resources/js/admin/bulk.js:75,104,127` and
  the 700ms-delayed reloads for signature actions) — two round trips per action.

## Targets for later phases

- **Queries < 15 per page** (GET render), dashboard included.
- **Production p75 TTFB < 300 ms** for admin GETs (measured at the edge/nginx,
  real data, warm caches) — ms-values above are not comparable to this target.
