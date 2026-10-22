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

## Supported versions

Only the current `main` / latest production release is supported. Fixes are not back-ported to older releases.

## Hardening notes for maintainers

- The public site runs under a strict CSP (no `unsafe-inline` for scripts); new inline scripts need an external file or a sha256 entry in `AddSecurityHeaders.php`.
- The admin panel allows `unsafe-inline`/`unsafe-eval` for Alpine only.
- Keyword moderation settings live in the `settings` table (group `moderation`); they are read with a direct query on the write path so a cache failure cannot silently drop them.
