# Audit slices

Audit the app one slice at a time instead of the whole repo. A slice is **mature** when every item in
"Done when" holds. Update the table after each audit; file findings with `/audit-to-issues` (max 15 per run).

## How to run a slice

1. `/ponytail:ponytail-audit` limited to the slice's paths (below). Verify evidence against `origin/main`.
2. `/audit-to-issues` files the findings as issues in Backlog on the project board.
3. Move cards to Ready in small batches; the loop fixes them, you review and merge.
4. After each deploy that touched the slice, spot-check the page by hand (CI cannot see third-party frames, mail, Cloudflare).
5. Re-audit the slice. When nothing new at `sev:high` or `sev:medium` turns up, mark it mature.

## Done when (every slice)

- Last audit found no open `sev:high` or `sev:medium` issue.
- Each fixed bug has a test (unit, feature or Playwright spec).
- The slice's production switches and settings are written down (this file or `docs/operations.md`).
- One manual check on production after the last deploy.

## Slices, in order

| # | Slice | Paths | Why this order | Status | Last audit | Issues |
|---|---|---|---|---|---|---|
| 1 | Petition create / edit / delete | `app/Http/Controllers/PetitionController.php`, `app/Http/Requests/PetitionRequest.php`, `app/Models/Petition.php`, `resources/views/petition/` | Core flow; first audit done | in progress | 2026-11-03 | #42-#50, mostly merged |
| 2 | Signing pipeline | `app/Services/PetitionSigning.php`, `app/Services/PetitionStats.php`, `signatures:reconcile` | Counts, spam, guest verification mail | not started | - | - |
| 3 | Mail | `app/Mail/`, `app/Support/MailQuota*`, `app/Console/Commands/Send*`, `docs/mail-sources.md` | Free-tier quota ran out in Oct 2026 | not started | - | - |
| 4 | Auth and accounts | `app/Http/Controllers/AuthController.php`, email-change flow, `WpSso` | Email change sets `verified=false`; verification gap noted | not started | - | - |
| 5 | Ads and CSP | `app/Support/Ads.php`, `resources/views/components/ads/`, `AddSecurityHeaders`, `CanonicalHost`, `docs/` | Outage on 2026-11-04 (`ads_frame_origin`) | not started | - | - |
| 6 | Admin panel | `app/Http/Controllers/Admin/`, permissions, audit log | Privileged actions | not started | - | - |
| 7 | Moderation and spam | `Spam`, `ReviewKeywords`, `HtmlSanitizer` | User content safety | not started | - | - |
| 8 | i18n and SEO | `lang/`, `app/Support/Seo.php`, `routes/web.php` locale group | 14 locales; #17 landed | not started | - | - |
| 9 | CI, deploy, ops | `.github/workflows/`, `deploy/`, `scripts/` | Pipeline hardened in #56; deploy smoke test is shallow | not started | - | - |

## Off limits

- `public/magazine/` (separate WordPress install).
- Off-server backups and SSH hardening (owner hold, 2026-11-02).
- Security findings: file as `sev:high` and withhold exploit details from the issue text.

## Notes

- Add a line here for each lesson that changes how a slice is audited (for example "check `ads_frame_origin` before any ads change").
