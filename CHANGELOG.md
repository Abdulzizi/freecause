# Changelog

All notable changes to FreeCause. Format: [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), versioning follows the deploy tags (`vYYYY.MM.DD-HHMMSS`).

## [v2026.10.25-070744]
### Added
- Legacy short links: `/{shortslug}` (6 chars) now 301s to the canonical petition URL. New `legacy_shortlinks` table, `legacy:import-shortlinks` command (idempotent) and a 165,529-pair dataset.
- Admin dashboard headlines **real users** (4.53M) and shows the 18.45M account total with the legacy nick-only count as context; verified % is computed on real users only. `User::real()` / `User::synthetic()` give the definition one home.
### Changed
- Ad slots rotate across static, auth, profile, form and error pages through one `x-ads.rotate` component; `banner468` is hidden below `md` (fixes the 640-767 three-block stack); the mobile infeed rotates through a mobile-safe pool.
- Comment rendering treats localized boilerplate as a default comment (Polish `Podpisuje się pod tą petycją`, Greek `αυτήν` form, punctuation variants), so it no longer counts as a real comment.
### Fixed
- Backups no longer report a false failure: `config/backup.php` maps every notification class to an empty channel list, so `BaseNotification::via()` no longer hits an undefined key (and no mail is attempted).
- `<x-ads.infeed />` without a `name` prop no longer throws `Undefined variable $name` on signatures and my-petitions.

## [Unreleased]
### Security
- Host patched for the php2shell advisories (CVE-2026-109618 / CVE-2026-109619): PHP 8.3.36 from the ondrej/sury repository, PHP-FPM pool hardened (`session.upload_progress.enabled=0`, `memory_limit=-1`, `pm.max_requests=500`), and the app now refuses to boot on an unpatched PHP (`ENFORCE_MIN_PHP`).
### Changed
- CI/CD: every push to `main` now deploys production through one pipeline (CI gate → Deployer 8 with the exact tested commit → smoke test → auto-rollback). `v*` tags are versioning only and publish a GitHub Release from the changelog section.


## [v2026.10.22-075231]
### Fixed
- i18n: added the missing `confirm_unsubscribe` string to all 13 non-English locales (the parity audit was failing).

## [v2026.10.22-074341]
### Added
- Admin review-keyword management at `/admin/keywords`: per-locale term lists, optional `term :: concept` grouping, global enable/disable per concept, coverage counts and a read-only view for moderators without edit rights.
- Keyword rescan: `php artisan review-keywords:rescan` and an admin button re-check published petitions against the current list and hold the matching ones (cursor + lease in settings, `spam:edit` **and** `petitions:edit` required).
- Admin petition sheet: shows the matched keyword with its locale, only while the petition is still held.
### Changed
- Keyword matching is union over all locale lists; a matched petition stays unpublished with the localized `petition.review_required` notice, a clean petition auto-publishes.
- Review-owned legacy spam words (`casino`, `porn`, `escort`) are held for review on the petition path instead of hard-rejecting the petition; derived wordings (`pornographic`) are still blocked.
- Keyword catalogue is cached (300 s + short process memo) and bypasses the cache when it misbehaves.

## [v2026.10.20-145737]
### Fixed
- Public petition pages link the latest signature name to the user profile.

## [Unreleased history]
Earlier releases are not itemised here; see the git history and the deploy tags.
