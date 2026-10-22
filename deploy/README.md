# Deploy

Production runs on the **Deployer 8** layout at `/var/www/freecause`
(`releases/`, `shared/`, `current`). nginx serves
`/var/www/freecause/current/public`. The old in-place install at
`/var/www/freecause-release-v2026.09.19` is retained, unmodified, for rollback.

## Layers

- `.github/workflows/ci.yml` — the gate: `composer validate`/install,
  `php artisan test`, Pint `--test`, `npm ci && npm run build`. Runs on PRs,
  pushes to `main`, and is called by `deploy.yml` (`deploy` needs `ci`).
- `.github/workflows/deploy.yml` — the single CD path. On a `v*` tag (or manual
  dispatch) it runs CI → Deployer deploy → `/version` smoke → auto-rollback on
  failure. Bound to the `production` GitHub Environment.
- `deploy/deploy.php` — Deployer recipe (release dirs, shared dirs, migrate,
  cache warm, queue restart, PHP-FPM reload).
- `deploy/supervisor/freecause-release-queue.conf` — supervisor program template.
- `deploy/supervisor/freecause-release-scheduler.conf` — supervisor program
  running `php artisan schedule:work`. **Must be installed** (symlink/copy into
  the supervisor conf.d and `supervisorctl reread && supervisorctl update`)
  alongside the queue program — without it the scheduler in `routes/console.php`
  (backups, `signatures:reconcile`, `cache:warm`, weekly digest, `queue:monitor`,
  log pruning) never runs. `schedule:work` targets the stable `current/` symlink,
  so it needs no per-release re-point.
- `deploy/cutover.sh` — seeds `shared/` from an in-place install (dry-run by
  default; `--apply` to execute).

## GitHub Environment (one-time)

Repo → Settings → Environments → `production`, with **Required reviewers**.
Secrets/vars: `DEPLOY_SSH_KEY` (secret), `DEPLOY_HOST`, `DEPLOY_PATH` (vars).
Without reviewers, `environment: production` is a no-op.

## Lockfile

`package-lock.json` is committed (explicit override of the repo's historical
"never commit package-lock.json" rule), so CI and deploy use `npm ci`.

## Recipe notes

- Host `production`: `DEPLOY_HOST`, `DEPLOY_USER` (default `root`),
  `DEPLOY_PATH` (default `/var/www/freecause`), `DEPLOY_IDENTITY`.
  The workflow pins `DEPLOY_PATH=/var/www/freecause`; the legacy `DEPLOY_PATH`
  repo variable (old release dir) is no longer read and should be deleted.
- Host `rehearsal`: a throwaway layout with `manage_services=false` so it cannot
  touch live supervisor/PHP-FPM.
- `bin/php` = `php8.3`; FPM service `php8.3-fpm`; `keep_releases` = 5.
- `default_timeout` = **900s** (Deployer defaults to 300). Set after
  2026-10-14, when `artisan:migrate` was killed at 300s while altering the
  18M-row `users` table: the run auto-rolled back but all 5 migrations had
  already committed (batch 13), leaving schema ahead of served code until the
  next deploy. The `users.password` nullability migration now runs an explicit
  `ALTER ... MODIFY ... ALGORITHM=INPLACE, LOCK=NONE` on MySQL (was a Blueprint
  `->change()` that could lock-copy the whole table); the remaining pending
  `users` migrations are nullable-column adds (INSTANT on MySQL 8) and
  index adds (INPLACE/LOCK=NONE), so none should take a metadata-blocking lock.
- **Pre-migrate snapshot:** `db:snapshot` runs before `artisan:migrate` (and
  before maintenance mode), `mysqldump --single-transaction`ing the DB to
  `shared/db-snapshots/pre-migrate-<ts>.sql.gz` (last 7 retained). It reads
  creds from `shared/.env`, passes the password via `MYSQL_PWD` (not argv), and
  refuses if under 2 GB free. Assumes self-hosted MySQL on the deploy host; for
  a managed DB (RDS etc.) replace this task with a provider snapshot API call.
- Assets are built in CI and uploaded (`deploy:assets`) — the server needs no
  Node. The upload targets `{{release_path}}/public` (rsync copies a directory in
  as its basename, so targeting `public/build` nested it as `public/build/build`).
- `deploy:version` reads the commit from `{{deploy_path}}/.dep/repo` (Deployer 8
  releases are not git repos) and stamps `storage/app/version.json` for `/version`.
- Maintenance mode wraps migrations only; it lifts immediately after the symlink
  flip (`after('deploy:symlink', 'artisan:up')`) so `cache:warm` (minutes) runs
  live. `sitemap*.xml` and `robots.txt` are excluded from maintenance via
  `preventRequestsDuringMaintenance()` in `bootstrap/app.php`, so crawlers never
  see a 503. On failure, `artisan:up` runs before `deploy:unlock`.

### Shared dirs (from the repo's `.gitignore`)

```
.env
storage
public/magazine/wp-config.php
public/magazine/.htaccess
public/magazine/wp-content/{uploads,cache,upgrade,upgrade-temp-backup,updraft,updraft_backup}
```

### Size note

`public/magazine` (WP core + plugins) is tracked and ~386 MB, so each release
carries its own copy; `keep_releases = 5` costs roughly 2 GB. A later phase
should deploy the WP tree separately or share it.

## PHP runtime requirements

Production runs **PHP 8.3** (`php8.3-fpm`), installed from the
**ondrej/sury** apt repository (`packages.sury.org/php/`, signed by
`/usr/share/keyrings/sury-php.gpg`) so patched point releases are available on
the day they ship.

Current host: **8.3.36** — the first release patched for the php2shell
advisories (CVE-2026-109618 / CVE-2026-109619, October 2026). The minimum
patched version is 8.3.36; the deploy pipeline prints the host PHP version and
warns when it is below that, and the app refuses to boot on an unpatched PHP
(`ENFORCE_MIN_PHP=true` in the shared `.env`, minimum in `config/app.php`).

Patches: `apt-get -y -o Dpkg::Options::="--force-confold" install
php8.3-cli=<v> php8.3-fpm=<v> php8.3-common=<v> php8.3-bcmath=<v>
php8.3-curl=<v> php8.3-gd=<v> php8.3-intl=<v> php8.3-mbstring=<v>
php8.3-mysql=<v> php8.3-opcache=<v> php8.3-readline=<v> php8.3-xml=<v>
php8.3-zip=<v>` — keep the conffiles (`--force-confold`) and the module list
identical to the host, then `systemctl reload php8.3-fpm` (graceful, no
downtime) and verify the version through the FPM socket with `cgi-fcgi`.

PHP-FPM pool settings (`/etc/php/8.3/fpm/pool.d/www.conf`) — these stay in
place after patching (they are the documented mitigations for the same CVEs):

```ini
php_admin_value[session.upload_progress.enabled] = 0   ; kills the confirmed session-lifecycle path
php_admin_value[memory_limit] = -1                    ; removes the predictable OOM trigger
php_admin_value[post_max_size] = 16M                  ; nginx caps bodies at 10M
php_admin_value[upload_max_filesize] = 8M             ; the app allows 4 MB images
pm.max_requests = 500                                  ; recycle workers, bound the stale-state window
```

Set them at pool level (admin values), never in `.user.ini` or via `ini_set()` —
those are applied too late. Reload FPM after changing them.

## Operations

- **Deploy:** every push to `main` runs the **Deploy** workflow (CI gate →
  Deployer 8 → smoke test → auto-rollback). The exact tested commit is
  shipped with `--revision=<sha>`; the pipeline refuses any target that is
  not on `main`.
- **Versioning:** a `v*` tag only publishes a GitHub Release (notes from the
  matching `CHANGELOG.md` section). Tags never deploy.
- **Rollback:** `git revert` on `main` and push, or run the **Deploy**
  workflow with `action=rollback`, or
  `dep -f deploy/deploy.php rollback production`.
- **Manual redeploy:** dispatch **Deploy** with `action=deploy` and `ref` = an
  older commit on `main` (blank = the dispatched ref).

## History

Rehearsed on a throwaway layout, then cut over in place with the old install
serving until the nginx reload (no downtime). Bugs caught and fixed along the
way: `deployer.org/deployer.phar` serves 6.x (pinned to `v8.0.5`); the version
task assumed a git repo in the release dir; the asset upload nested
`public/build/build` and 500'd the site. The first cutover release carried the
nested-build bug and was removed, so the earliest rollback target is release 2.
