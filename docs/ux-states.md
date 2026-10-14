# UX States

Status: **shipped** for the public site. Guard: `bash scripts/ds-lint.sh`
(includes `scripts/ds-lint-a11y.py`). Plan: `/Users/user/.opencode/plan/ux-remediation-plan.md`.

This is the contract for the non-happy-path states the public site must render.
Desktop (≥992px) keeps the baseline layout; every addition below is gated behind
`max-md:`/`md:` or is copy/behaviour only.

## Accessibility gate

`scripts/ds-lint-a11y.py` is a static scan over `resources/views` (excluding
`blatui`, `emails`, `admin` — admin is a BlatUI non-goal, tracked separately).
It fails on:

| Rule                   | Defect                                                     |
| ---------------------- | ---------------------------------------------------------- |
| `img-alt`              | `<img>` without an `alt` attribute                         |
| `unlabelled-control`   | `input`/`select`/`textarea` with no `label for`, wrapping label, `aria-label*` or `title` |
| `empty-link`           | `<a>` with no accessible name                              |
| `empty-button`         | `<button>` with no accessible name                         |
| `focus-in-aria-hidden` | focusable element inside `aria-hidden="true"`              |

`scripts/ds-lint-a11y-allow.txt` holds one `path:rule-id` per line for genuine
false positives of the static checker; never add a real defect to it.

## State matrix

| Surface                | Loading            | Empty                         | Error / offline                  | Success                        |
| ---------------------- | ------------------ | ----------------------------- | -------------------------------- | ------------------------------ |
| Petition listing       | `x-ui.skeleton`    | `x-ui.empty`                  | busy lock + inline error         | —                              |
| Petition detail        | —                  | —                             | closed → `petition.offline`      | thanks-for-signing             |
| Sign / claim form      | busy lock          | —                             | field error / network notice     | claim → auto-login             |
| Petition create/edit   | —                  | —                             | field error                      | redirect to draft              |
| Contact form           | busy lock          | —                             | field error / network notice     | inline `#contactSuccess` panel |
| Auth: register         | —                  | —                             | error alert                      | verify-email screen            |
| Auth: login            | —                  | —                             | invalid credentials              | redirect to destination        |
| Auth: unverified login | —                  | —                             | redirect to verify screen        | —                              |
| Email verification     | —                  | —                             | expired / invalid token          | auto-login or “verified” page  |
| Password reset         | —                  | —                             | field error                      | set → auto-login               |
| FAQ                    | —                  | `data-faq-search-empty`       | —                                | client-side filter             |
| Global search (desktop)| —                  | recent-search datalist        | —                                | navigates to listing           |

## Landing / redirect table (verification)

| Entry point                                    | Session flag          | Lands on                                    |
| ---------------------------------------------- | --------------------- | ------------------------------------------- |
| Register (verification required)               | `verify_autologin`    | `verification.notice` (`/:locale/verify-email`) |
| Sign with unseen email (verification on)       | —                     | `verification.notice`                        |
| Click verify link, same session as register    | `verify_autologin`    | auto-login → intended / petition             |
| Click verify link, other session               | —                     | `verification.verified` (`/email-verified`)  |
| Login as unverified user                       | —                     | `verification.notice` + `email` error        |
| Claim account (set password)                   | —                     | auto-login → profile completion / petition   |
| Password reset                                 | —                     | auto-login → intended                        |

Admin counterparts: un-verifying a user rotates the token and queues a fresh
link (blocked with a clear warning when SMTP is off), and changing a verified
user's email forces the same re-verification flow. The user drawer shows the
last-sent timestamp and a one-click resend.

## Low-bandwidth / duplicate submit

`public/legacy/js-v2/form-busy.js` covers **every** POST form on the public
site: it disables the submit control while the request is in flight, injects a
`fcui-field__error` message when the network fails, and restores the control on
`pageshow` (bfcache back-navigation). No inline scripts; the public CSP forbids
them.

## Closed petitions

A published-but-inactive petition renders `resources/views/petition/offline.blade.php`:
closed badge, closed-on date (`updated_at`), final signature count, up to three
related petitions, and next actions. Signature UI is absent.

## Trending

`?sort=trending` orders the listing by **confirmed** signatures in the last 7
days (cached 10 minutes per locale + page), then total signatures, then id.
Petitions without recent signatures still appear lower down; it is an order,
not a filter.
