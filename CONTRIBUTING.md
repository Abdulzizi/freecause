# Contributing

## Setup

```bash
composer setup
npm install
cp .env.example .env && php artisan key:generate
php artisan migrate
```

Local PHP binary on macOS: `/opt/homebrew/opt/php/bin/php`. Local DB: `freecause_local` MySQL in Docker on `:33306` (may be down) — prefer sqlite for CLI work.

## Checks before you commit

```bash
CACHE_STORE=array DB_CONNECTION=sqlite DB_DATABASE=:memory: /opt/homebrew/opt/php/bin/php artisan test
/opt/homebrew/opt/php/bin/php ./vendor/bin/pint app database tests routes
bash scripts/ds-lint.sh
/opt/homebrew/opt/php/bin/php scripts/lang-audit.php
```

`pint` needs an explicit path list — a bare run reformats the WordPress tree under `public/magazine/`.

## Branch model (this repo — small/solo team)

```
main                              → production (always deployable)
feat/{category}/{feature}         → new work
fix/{category}/{fix}              → bug fix
```

- **Categories**: `admin`, `petitions`, `i18n`, `moderation`, `ui`, `deploy`, `perf`, `spam`.
- Branch from `main`, rebase on `main` while you work, keep branches short-lived (< 1 week).
- One concern per branch. Small fixes may go straight to `main` when they touch one file.
- No long-lived `staging` branch: there is a single deploy target, so "staging" would be production with extra steps. Use a feature branch + PR review instead.

### Enforcement (private repo on the Free plan)

GitHub branch protection and rulesets need **Pro** for a private repository, so the convention is enforced in-repo:

- `branch-lint` (`.github/workflows/branch-lint.yml`) fails any push or PR whose branch is not `feat/{category}/{feature}` or `fix/{category}/{fix}`.
- `main` deploys only through a `v*` tag that passed the CI gate — a direct push to `main` cannot reach production on its own.
- No force pushes, no branch deletion, no rewrite of published history.

## Releases (production)

`main` is deployed **by tag**, never by branch push:

```bash
git tag -a v$(date +%Y.%m.%d-%H%M%S) -m "…" && git push origin v…
```

`v*` → `ci.yml` gate (`test`, `assets`, `security-audit`) → `deploy.yml` (Deployer 8) → `37.60.254.112`. Migrations, cache warm, queue and PHP-FPM reload are part of the recipe (`deploy/README.md`).
A dev-dependency advisory (e.g. vite) fails `npm audit` and **blocks the deploy** — run `npm audit fix`, commit the lockfile, re-tag.
Rollback: dispatch the **Deploy** workflow with `action=rollback` and the old tag as `ref`.

## Team model (once a second maintainer exists)

```
main                              → production
staging                           → pre-production (shared integration branch)
feat/{category}/{feature}
fix/{category}/{fix}
```

- `staging` gets the same CI; the deploy workflow gains a staging target (separate `environment:` + host), not a second tag scheme.
- Promotion is a PR `staging → main` after staging is green; hotfixes branch from `main` and merge back into `staging`.
- Add branch protection on `main` (required CI + one review) when reviews become real.

## Commits

- Conventional Commits: `feat`, `fix`, `test`, `chore`, `docs`, `refactor`.
- Stage files explicitly — never `git add -A` / `git commit -a`. Never commit `.env*` or secrets.
- `package-lock.json` is tracked: commit security bumps with the change that needs them.
- Keep the diff small; one logical change per commit.

## Pull requests

- PR from a `feat/…` or `fix/…` branch into `main`.
- CI must pass: `test`, `assets`, `security-audit`. A red `i18n parity` (every key in `lang/en` must exist in every locale) is not blocking but must be fixed before release.
- Describe **what changed and how it was verified** (command + result).
- Merge by squash or rebase, then delete the branch.

## Definition of done

- `php artisan test` green, `pint` clean, `scripts/ds-lint.sh` clean, `scripts/lang-audit.php` OK.
- Admin UI follows `docs/admin-ui.md` (BlatUI components, design tokens, no `!important`).
- New public strings exist in **all** locales (`lang/en` is the reference).
- Docs updated when behaviour, routes, tokens or the deploy flow change.
- A reviewer pass over the diff before commit (see `AGENTS.md`).

## Agent rules

`AGENTS.md` (local-only) holds the agent-facing rules: mandatory `ponytail` + `caveman` skills, autonomy limits, admin/design-system vocabulary, keyword moderation internals, deploy gate gotchas.
