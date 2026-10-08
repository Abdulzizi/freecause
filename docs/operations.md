# Operations

Single VPS (Ubuntu 24.04): nginx + PHP 8.3-FPM + MySQL 8. The app runs from
`/var/www/freecause/current` (Deployer layout — see [`../deploy/README.md`](../deploy/README.md)).
Supervisor group `freecause-release-queue:*` (2 workers, `www-data`). Daily
`backup:run` at 02:00 to the S3 `backups` disk, retention cleanup at 02:30.

Scale (approximate): users ~18M, signatures ~18M, petitions ~165k.

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
