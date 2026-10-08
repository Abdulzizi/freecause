# Deploy

Phase 0 ships the CI gate and the Deployer recipe. The recipe is authored but
**not yet executed** — production still deploys through the in-place SSH path in
`.github/workflows/deploy.yml`. Cutting over to Deployer is Phase 1 and needs
server prep plus an explicit go.

## Layers

- `.github/workflows/ci.yml` — the gate: composer validate/install, `php artisan
  test`, Pint `--test`, `npm install && npm run build`. Runs on PRs, pushes to
  `main`, and is called by `deploy.yml` (`deploy` needs `ci`).
- `.github/workflows/deploy.yml` — CI gate → build assets → server deploy →
  version stamp → smoke. Bound to the `production` GitHub Environment.
- `deploy/deploy.php` — Deployer 8 recipe (release dirs, shared dirs, migrate,
  cache warm, queue + supervisor + PHP-FPM reload).
- `deploy/supervisor/freecause-release-queue.conf` — supervisor program template.

## GitHub Environment setup (required, one-time)

1. Repo → Settings → Environments → New environment: `production`.
2. Add **Required reviewers** (whoever approves a prod deploy).
3. Optionally restrict to tag patterns and set a wait timer.
4. Secrets/vars used by `deploy.yml`: `DEPLOY_SSH_KEY` (secret),
   `DEPLOY_HOST`, `DEPLOY_PATH` (vars).

Until the environment exists with reviewers, `environment: production` is a
no-op and a tag still deploys immediately.

## Known constraint: no committed lockfile

`package-lock.json` is intentionally not committed, so CI/deploy use
`npm install` (not `npm ci`) and cannot use the Node cache action. Frontend
builds are therefore not byte-reproducible. Committing the lockfile would fix
this; that needs an explicit override of the repo's "never commit
package-lock.json" rule.

## Deployer recipe notes

Configured against Deployer 8 (`require 'recipe/laravel.php'`):

- Host `production` reads `DEPLOY_HOST`, `DEPLOY_USER` (default `root`),
  `DEPLOY_PATH` (default `/var/www/freecause`), `DEPLOY_IDENTITY` (default
  `~/.ssh/id_deploy`).
- `bin/php` = `php8.3`; PHP-FPM service `php8.3-fpm`.
- Assets are built in CI and uploaded (`deploy:assets`), so the server does not
  need Node.
- `deploy:version` stamps `storage/app/version.json` for the `/version`
  endpoint.
- Maintenance mode wraps migrations (`before deploy:migrate → artisan:down`,
  `after deploy:success → artisan:up`), then `cache:warm`, `queue:restart`,
  supervisor restart, and a graceful PHP-FPM reload.
- `keep_releases` = 5.

### Shared dirs (from the repo's `.gitignore`)

These are runtime data and must live in `shared/`, or they are lost between
releases:

```
.env
storage
public/magazine/wp-config.php
public/magazine/.htaccess
public/magazine/wp-content/uploads
public/magazine/wp-content/cache
public/magazine/wp-content/upgrade
public/magazine/wp-content/upgrade-temp-backup
public/magazine/wp-content/updraft
public/magazine/wp-content/updraft_backup
```

On cutover, seed `shared/` from the current install before the first deploy
(move the live uploads/config into `shared/`, keep a copy) so nothing is lost.

### Size note

`public/magazine` (WP core + plugins) is tracked and ~386 MB, so each release
carries its own copy. `keep_releases = 5` therefore costs roughly 2 GB. A later
phase should either treat the WP tree as a separate deploy or share it.

## Cutover (Phase 1, needs approval)

1. Provision the Deployer layout at `deploy_path` (`releases/`, `shared/`,
   `current`). New path, not the existing release dir.
2. Seed `shared/` (see above) from the live install.
3. Repoint nginx `root` to `{deploy_path}/current/public`; back up the vhost,
   `nginx -t`, reload.
4. Install `deploy/supervisor/freecause-release-queue.conf` on the server and
   `supervisorctl reread && update`.
5. Rehearse end-to-end on a throwaway host or a copied DB before the real flip.
6. Run `dep deploy` from the runner; verify `/version` shows the new commit and
   `/up` is 200.

## Rollback

- Deployer: `dep rollback` (re-points `current` to the previous release).
- `deploy.yml` failure hook: re-run with `ref` set to the previous tag.
