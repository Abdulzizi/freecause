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
  cache warm, queue + supervisor + PHP-FPM reload).
- `deploy/supervisor/freecause-release-queue.conf` — supervisor program template.
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
- Host `rehearsal`: a throwaway layout with `manage_services=false` so it cannot
  touch live supervisor/PHP-FPM.
- `bin/php` = `php8.3`; FPM service `php8.3-fpm`; `keep_releases` = 5.
- Assets are built in CI and uploaded (`deploy:assets`) — the server needs no
  Node. The upload targets `{{release_path}}/public` (rsync copies a directory in
  as its basename, so targeting `public/build` nested it as `public/build/build`).
- `deploy:version` reads the commit from `{{deploy_path}}/.dep/repo` (Deployer 8
  releases are not git repos) and stamps `storage/app/version.json` for `/version`.
- Maintenance mode wraps migrations, then `cache:warm`, `queue:restart`,
  supervisor restart, and a graceful PHP-FPM reload.

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

## Operations

- **Deploy:** push a `v*` tag (or run the **Deploy** workflow with `action=deploy`).
- **Rollback:** run the **Deploy** workflow with `action=rollback`, or
  `dep -f deploy/deploy.php rollback production`.
- **Manual redeploy:** dispatch with `ref` = a tag (blank deploys the current
  branch).

## History

Rehearsed on a throwaway layout, then cut over in place with the old install
serving until the nginx reload (no downtime). Bugs caught and fixed along the
way: `deployer.org/deployer.phar` serves 6.x (pinned to `v8.0.5`); the version
task assumed a git repo in the release dir; the asset upload nested
`public/build/build` and 500'd the site. The first cutover release carried the
nested-build bug and was removed, so the earliest rollback target is release 2.
