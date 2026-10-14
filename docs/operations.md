# Operations

Single VPS (Ubuntu 24.04): nginx + PHP 8.3-FPM + MySQL 8. The app runs from
`/var/www/freecause/current` (Deployer layout — see [`../deploy/README.md`](../deploy/README.md)).
Supervisor group `freecause-release-queue:*` (2 workers, `www-data`). Daily
`backup:run` at 02:00 to the S3 `backups` disk, retention cleanup at 02:30.
`emails:petition-digest` runs weekly (Monday 08:00) — per-owner petition digest,
gated on `verified` + `notify_product`; scheduled in `routes/console.php`.

Scale (approximate): users ~18M, signatures ~18M, petitions ~165k.

## Monitoring

Sentry error tracking (`sentry/sentry-laravel`, wired in `bootstrap/app.php`
via `Integration::handles`). DSN lives in the server's shared `.env` as
`SENTRY_LARAVEL_DSN`; `SENTRY_TRACES_SAMPLE_RATE` is optional (0.1 in prod).
Verify with `php artisan sentry:test`. Committed: package, config, bootstrap
wiring — the DSN itself never lands in git.

## Operational traps

- **Pint**: always pass explicit paths (`app database tests routes config`); a
  bare run reformats the WordPress tree under `public/magazine/`.
- **Migrations and locale helpers**: resolving locale URLs (`lroute()`, etc.)
  inside a migration seeds empty language caches and can 302-loop later requests
  in the same process. Migrations should use raw token markup.
- **Mail** is DB-settings driven; restart queue workers after changing
  mail/settings rather than redeploying.
- **Large-table DDL** (users/signatures ~18M rows) can take minutes — plan a
  window and prefer online DDL for new indexes.
- **Cloudflare** can serve stale files at the edge; purge by URL after
  robots/config changes whose freshness matters.

## Edge (Cloudflare)

Zone `freecause.com` is on the Cloudflare **Free** plan. Custom WAF rules are
capped at **5** — repurpose a rule rather than expecting a 6th. Inventory and
bot-traffic findings: [`edge-waf-bots.md`](edge-waf-bots.md).

- Free-plan custom-rule expressions reject `matches` and `starts_with`
  (Business/Advanced): use `eq`, `contains`, `and`, `not`, `lower()`.
- The MCP/API token can **list + delete** custom rules but **create** fails with
  `405 Method not allowed for this authentication scheme` — add/edit rules in the
  Cloudflare dashboard.
- `robots.txt` blocks Applebot (`46db1e79`, 2026-10-07); it had crawled ~1.26M
  requests/week beforehand (verified-crawler skip on profile paths) — expect a
  multi-day decline.
- 503 spikes track deploy maintenance windows + crawler retries (Oct 8: 6.2K);
  deploy fix `c02a8117` lifts maintenance earlier.

## Parked backlog

- Brevo SMTP activation (dashboard action).
- Rotate the Google OAuth client secret and load it into the prod `.env`.
- WordPress hardening: rotate WP salts / SSO secret, review `wp-file-manager`,
  consider denying direct PHP execution under `/magazine/`.
- Reconcile nginx drift (`sites-enabled` vs `sites-available`) and reload.
- Cloudflare WAF 403s on some legacy locale paths (`it_IT/utente`,
  `es_ES/usuario`): the 301 works origin-side but is challenged at the edge.
- binlog expiry needs a MySQL restart — schedule it.
- Email-verification gating: product decision for create/edit/delete petitions.
- CKEditor 4.21 is EOL — decide on a self-hosted/updated bundle.
- Contacts form on the DB-driven page: wire the live POST form or leave it text-only.
- Signature counts: denormalized `signature_count` vs computed.
