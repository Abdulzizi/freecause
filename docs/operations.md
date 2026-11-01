# Operations

Single VPS (Ubuntu 24.04): nginx + PHP 8.3-FPM + MySQL 8. The app runs from
`/var/www/freecause/current` (Deployer layout — see [`../deploy/README.md`](../deploy/README.md)).
Supervisor group `freecause-release-queue:*` (2 workers, `www-data`) plus
`freecause-release-scheduler` (`schedule:work`). Schedule lives in
`routes/console.php` (`php artisan schedule:list` on the host).

Scale (approximate): users ~18M, signatures ~18M, petitions ~165k.

## Host (verified 2026-11-01)

- Contabo VPS, one host for everything: 6 vCPU, 12 GB RAM, 4 GB swap, 193 GB
  disk (31% used). Kernel `6.8.0-147`; reboots on 2026-10-18 and 2026-10-31
  17:33 (clean shutdown, no OOM in the kernel log).
- Disk: MySQL datadir ~28 GB, `shared/` ~16 GB, `releases/` ~2.2 GB,
  `/var/log` ~865 MB. systemd journal capped at 200 MB
  (`/etc/systemd/journald.conf.d/size.conf`).
- PHP-FPM: `pm = dynamic`, `max_children` 60, `start_servers` 10, spare 3–20,
  `max_requests` 500 (~57 MB per worker). Opcache 256 MB, 20000 files,
  `opcache.validate_timestamps=0` in the FPM `php.ini`; `deploy.php` reloads FPM after `deploy:symlink`.
  Check FPM settings through the FPM `php.ini`, not `php -i` (CLI differs).
- MySQL 8 on `127.0.0.1`, database `freecause_release_20260919`. Binlogs
  kept 7 days (`binlog_expire_logs_seconds` 604800), redo log 1 GB
  (`innodb_redo_log_capacity`); both set with `SET PERSIST`, no restart needed.
  Redis on `127.0.0.1:6379` (cache and session; `phpredis`). Queue driver is
  `database`, not Redis.
- nginx: gzip on, no brotli. `sites-enabled/freecause-com` is a symlink to
  `sites-available/freecause-com`; edit the latter.
- Firewall: `ufw` active.

## Backups (verified 2026-11-01)

- `backup:run` daily 02:00, `backup:clean` 04:00 (`keep_all_backups_for_days`
  7), `backup:monitor` 06:00, via `spatie/laravel-backup`. Output goes to
  `storage/logs/backup.log`.
- On-host only: the `backups` disk uses the `local` driver. 7 daily archives
  (~2 GB each, database only, uploads not included) in
  `shared/storage/app/backups/FreeCause/<ts>.zip`.
- Root-only copy in `/root/backup-vault/` (weekly zip, keep 4; daily uploads
  rsync) from `/etc/cron.d/freecause-backup-vault`.
  **There is no off-host copy.**
- 2026-10-28 02:00 `backup:run` was killed (exit code 143).
- Deploy snapshots: `db:snapshot` writes `shared/db-snapshots/pre-migrate-<ts>.sql.gz`
  (~2 GB each, last 7 kept).
- Restore tested 2026-11-01: the 02:00 archive loaded into a scratch database in
  1h51m. `users`, `signatures`, `petitions` row counts and the table count (72)
  matched live. Views, routines and triggers were not compared.

## Security state (verified 2026-11-01)

- `fail2ban` is installed but **disabled and stopped** (stopped 2026-10-31
  17:28, not enabled at boot). The auth log shows continuous SSH password
  attempts against `root`, `ubuntu` and `deploy`.
- Effective `sshd` settings: `PermitRootLogin yes`
  (`sshd_config.d/00-rootpw.conf` overrides `prohibit-password` in
  `sshd_config`) and `PasswordAuthentication yes`
  (`50-cloud-init.conf` is read before `60-cloudimg-settings.conf`, and sshd
  takes the first value). Confirm with `sshd -T`.

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
  window and prefer online DDL for new indexes. Remote commands run with
  Deployer's `default_timeout`, raised **300 → 900s** in `deploy/deploy.php`
  after a 300s kill on 2026-10-14 (`add_gender_to_users` on the 18M-row `users`
  table). Migrations that already recorded in `migrations` do not re-run, so a
  rollback after such a kill leaves the schema ahead of the served code until
  the next deploy.
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

- Enable `fail2ban`; set `PermitRootLogin prohibit-password` and
  `PasswordAuthentication no`; reload `ssh`.
- smtp2go SMTP activation (dashboard action).
- Rotate the Google OAuth client secret and load it into the prod `.env`.
- WordPress hardening: rotate WP salts / SSO secret, review `wp-file-manager`,
  consider denying direct PHP execution under `/magazine/`.
- Cloudflare WAF 403s on some legacy locale paths (`it_IT/utente`,
  `es_ES/usuario`): the 301 works origin-side but is challenged at the edge.
- Email-verification gating: product decision for create/edit/delete petitions.
- CKEditor 4.21 is EOL — decide on a self-hosted/updated bundle.
- Contacts form on the DB-driven page: wire the live POST form or leave it text-only.
- Signature counts: denormalized `signature_count` vs computed.
