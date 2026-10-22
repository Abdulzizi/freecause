# Security Policy

## Reporting a vulnerability

Do **not** open a public issue. Report privately through the repository's **Security → Report a vulnerability** form (GitHub private advisory), or via the site's support/contact form, with:

- what is affected (route, feature, locale),
- how to reproduce (steps, payload),
- the impact you observed.

You will get an acknowledgement within a few days. Fixes ship through the normal tag-triggered deploy; critical issues are fast-tracked.

## What counts as a vulnerability here

- Authentication or authorisation bypass in the admin panel (e.g. reaching `/admin/*` without a permission).
- Stored or reflected XSS on the public site or in the admin (Blade output that is not escaped).
- CSRF on a state-changing route that is not covered by `@csrf`.
- SQL injection, command execution, path traversal, unsafe deserialisation.
- Moderation bypass: a petition that should be held for review going live, or an admin-only action becoming reachable.
- Leaking another user's data (signatures, petitions, private messages, tokens) or any secret (`.env`, API keys, mail credentials).
- Spam/DoS vectors that let one anonymous actor hold the site down (the contact form, signups, petition creation, signature posting).

## Out of scope

- The WordPress tree under `public/magazine/` (a separate, client-operated install).
- Findings that need an already-compromised admin account.
- Missing security headers on third-party assets, or `X-Powered-By` style noise.
- Rate-limit gaps that only affect an authenticated admin.

## Active advisories

### php2shell — CVE-2026-109618 / CVE-2026-109619 (critical, unauthenticated RCE)

Disclosed 20–21 October 2026. Two critical memory-corruption flaws in **PHP core** (CVSS 9.2) that allow
unauthenticated remote code execution as the web worker:

- **CVE-2026-109618** (GHSA-w8hw-w9fx-j79w) — memory corruption during out-of-memory handling: PHP used
  `longjmp()` to abort a request on OOM, which could leave request memory half-initialised; the shutdown
  procedure then reads that invalid state. Crafted multipart requests (missing/unusable `Content-Length`,
  unbounded per-part headers, deeply nested field names, empty file controls) reach OOM even inside
  `post_max_size`.
- **CVE-2026-109619** (GHSA-6wmf-4rv5-p3rp) — stale global state across requests: state pointing into freed
  request memory survives into a later request served by the same worker. Fatal errors/timeouts interrupt the
  cleanup; `session.upload_progress` (enabled by default) is an early consumer of the stale state, and on PHP 8.5
  `fatal_error_backtraces` (default on) is a confirmed path.

**Patched versions:** 8.2.35 · 8.3.36 · 8.4.27 · 8.5.12 (8.1 and older get limited source backports).
Sources: [PHP 8.3.36 announcement](https://www.php.net/releases/8_3_36.php) ·
[CERT-EU 2026-018](https://cert.europa.eu/publications/security-advisories/2026-018) ·
[CISA alert](https://www.cisa.gov/news-events/alerts/2026/10/21/critical-vulnerabilities-php-require-immediate-patching) ·
[Rapid7 analysis + mitigations](https://www.rapid7.com/blog/post/etr-cve-2026-109618-and-cve-2026-109619-critical-unauthenticated-remote-code-execution-in-php) ·
[GHSA-w8hw-w9fx-j79w](https://github.com/php/php-src/security/advisories/GHSA-w8hw-w9fx-j79w) ·
[GHSA-6wmf-4rv5-p3rp](https://github.com/php/php-src/security/advisories/GHSA-6wmf-4rv5-p3rp).

Nothing in the application code can fix this — it is a runtime flaw. The mitigation is the PHP version plus
the PHP-FPM settings below. The app refuses to run on an unpatched PHP once `ENFORCE_MIN_PHP=true`, and
`GET /version` reports the running PHP version.

## Supported versions

Only the current `main` / latest production release is supported. Fixes are not back-ported to older releases.

## Hardening notes for maintainers

- The public site runs under a strict CSP (no `unsafe-inline` for scripts); new inline scripts need an external file or a sha256 entry in `AddSecurityHeaders.php`.
- The admin panel allows `unsafe-inline`/`unsafe-eval` for Alpine only.
- Keyword moderation settings live in the `settings` table (group `moderation`); they are read with a direct query on the write path so a cache failure cannot silently drop them.
