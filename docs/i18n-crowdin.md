# Crowdin Integration Plan

Crowdin / i18n integration runbook (static UI strings and DB content sync).

## Implementation status

| Item | Status |
|---|---|
| Crowdin project `freecause` (id `936363`) | ✅ exists, file-based, source `en`, 13 targets |
| Phase 0 pilot (PHP parser + API behavior) | ✅ done (see findings below) |
| `crowdin.yml` | ✅ added at repo root |
| CI: parity gate + Crowdin upload/download | ✅ `.github/workflows/i18n.yml` |
| Placeholder safety net in `scripts/lang-audit.php` | ✅ extended (was key-only) |
| Initial source upload + translation seeding | ✅ done — 20 sources, 12 locales at 87–98% |
| CI: parity, source upload, translation PR | ✅ verified in GitHub Actions (PR #1 opened) |
| DB content (petitions/pages/categories/CMS) | ⏳ Phase 2: `page_contents` + categories MT'd for 12 locales (see below); editorial pages + petitions pending |

### Phase 1 round-trip results (download verification)

Seeded the project: `lang/en/*.php` → 20 source files / 536 phrases; existing
git translations imported, so the 12 target locales now sit at **87–98%**
(`fr` 98%, `it` 95%, others ~88%); approvals are still 0% (nothing reviewed).

Findings from `upload sources` → `upload translations` → `download translations`:

1. **No translation values were lost.** A semantic (key→value) comparison of
   every downloaded file against git showed only the intended `sign.title`
   removal.
2. **Crowdin reorders keys** to match the English source order and re-aligns
   `=>`. That produces a large but benign ~120-file diff. Run
   `pint lang` after every download.
3. **A machine translation had been created for one string** (`da/contacts`
   `intro`) with `provider: "crowdin"` — it reworded the sentence and broke the
   email (`hello@freecause. om`). The download picked the MT version over the
   imported human one. The bad translation was **deleted**; only the imported
   version remains. Do **not** run auto-translate/pre-translate without
   reviewing; consider an approval step for human translations.

### Phase 0 pilot findings (important)

1. **PHP parser works.** Uploading `lang/en/userprofile.php` produced correct
   string identifiers (`array['home']`, …) and text, including HTML-free and
   colon text (`'Registration date :'`).
2. **The API flattens paths.** `add_file` with `name: lang/en/userprofile.php`
   stored the file as `/userprofile.php` — Crowdin treats directories as
   separate entities. Only the **Crowdin CLI with `preserve_hierarchy`** (or
   explicit directory creation) reproduces the `lang/en/…` layout. The CLI is
   therefore required for upload/sync; the MCP/API is not used for bulk upload.
   (Pilot file was deleted; project is clean.)
3. **Laravel `:name` placeholders are NOT recognized by Crowdin.** The system
   placeholder `wrappedColon` is documented as "colon on both sides, *unlike
   Laravel's `:name`*". There is no built-in Laravel placeholder and no MCP tool
   for custom placeholders — a **custom regex placeholder must be added in the
   Crowdin UI**: Project Settings → Placeholders → Custom, pattern
   `:[a-zA-Z_][a-zA-Z0-9_]*`.
4. Because of (3), CI now validates placeholder parity locally in
   `scripts/lang-audit.php`. It immediately found a pre-existing issue:
   `lang/*/sign.php`'s unused `title` key mismatched `:title`. The dead,
   redundant key was removed from all 14 locales (the used key is
   `sign.page_title`, which is correct everywhere).

### Phase 2 (DB content) — implemented

- Commands added: `translations:export-db` (English `page_contents` + category
  names → `crowdin-content/source.json`) and `translations:import-db {locale}`
  (upserts rows back into the DB from `crowdin-content/{locale}.json`).
- `crowdin.yml` gained the `crowdin-content/source.json` → `%two_letters_code%.json` entry.
- Flow: export → `upload sources` → MT pre-translation in Crowdin →
  `download translations` → `import-db` per locale → `cache:clear`.
- Result: `page_contents` and category translations are now fully populated for
  **12 locales** (`fr`, `it` human; `da de el es nl pl pt ro ru sv` MT). Turkish
  (`tr`) deliberately skipped (no MT support).

Residual (addressed): strings whose repo value was already English-ish were
imported as translations, so MT pre-translation skipped them. A one-off MT pass
over case-insensitive English matches (skipping HTML strings) fixed the visible
leak (`home.btn_create_petition`, 10 locales); formatting re-checked with `pint`
and the corrected values re-uploaded to Crowdin. Remaining "identical" values are
legitimate cognates/proper nouns (Email, PDF, FAQ…). Turkish (`tr`) still has
untranslated strings because no MT engine supports it.

## 1. Goal

Move Freecause's UI translation workflow onto Crowdin so translators work in a
managed platform while the repo stays the source of truth and deploys stay under
our control. Content (petitions, editorial pages, categories, CMS strings) is in
scope eventually, but only after static UI strings are wired up.

## 2. Current state

### Repo

- **14 locales**, source `en`: `da de el en es fr it nl pl pt ro ru sv tr`.
- **`lang/{locale}/*.php`** — 22 flat PHP-array files per locale, ~537 keys in
  `en`, placeholders in Laravel style (`:count`, `:name`, `:login_url`, …).
  `emails.php` carries transactional/digest mail copy (new keys land in `en` and
  are copied as English into the other locales until Crowdin catches up).
- Existing translations are **partial**: some keys are real (`fr home.h1`),
  others fall back to English (`es/tr/ru home.h1`).
- Locale resolution: `app/Http/Middleware/SetLocale.php` drives `/xx/` URL
  prefixes, browser auto-redirect, session, `URL::defaults`; helpers
  `lroute()` / `locale_url()` in `app/helpers.php`; active set from the
  `languages` DB table (`is_active`, `is_default`).
- **DB-translatable content, admin-edited, no MT today:**
  | Table | Fields | Admin |
  |---|---|---|
  | `petition_translations` | title, slug, description | `Admin/AdminPetitionsController` |
  | `page_translations` | title, slug, content, published | `Admin/AdminPagesController` |
  | `category_translations` | name, slug | `Admin/AdminCategoriesController` |
  | `page_contents` | page, locale, key, value | `Admin/LanguageOptionsController` |
  `page_contents` already falls back to `__()` lang keys in Blade.
- **Existing i18n tooling:** `php artisan translations:audit`,
  `translations:consolidate`, `TranslationParityHarness`, and
  `scripts/lang-audit.php` run by `.github/workflows/i18n.yml`. The audit now
  checks files + keys + `:placeholders`.
- Dead/legacy: `resources/lang/{locale}/{messages,form,petition,auth}.php`
  (partial duplicates of `lang/`); `app/Support/Locale::trans()` reads a
  `messages.*` namespace that does not exist under `lang/`.
- **Not translated:** `resources/views/emails/*` (hardcoded English).

### Crowdin

- Project **Freecause** (`freecause`, id `936363`), **file-based**, source `en`,
  **13 targets**: `ro fr es-ES da de el it nl pl pt-PT ru sv-SE tr`.
- Project is **empty** (no files uploaded yet).
- MT allowed; default **TM 752125** and **glossary 734963** assigned;
  `languageMapping` is **empty**; pre-translation (AI/MT/TM) off.

## 3. Principles

1. **Repo is the source of truth.** Crowdin proposes, PRs merge.
2. **Never point Crowdin at the database.** DB content is exported/imported
   through explicit artisan commands, never edited live by the API.
3. **Small tool surface first.** Static `lang/en/*` only in Phase 1.
4. **Never overwrite reviewed work.** Seed existing translations before any
   download; use unapproved-only updates.
5. **Reuse the existing parity gate** (`scripts/lang-audit.php`) as the CI
   contract.

## 4. Phases

### Phase 0 — Project prep & pilot ✅ done

Pilot results and API caveats are in *Implementation status* above. Remaining
Phase 0 action: add the **custom placeholder** regex in the Crowdin UI
(`:[a-zA-Z_][a-zA-Z0-9_]*`).

### Phase 1 — Static UI strings ⏳ in progress

Done:

- `crowdin.yml` (see §5) using `%two_letters_code%` → `es/pt/sv` match the repo
  directories with no Crowdin language mapping.
- `.github/workflows/i18n.yml` (§6): parity gate on every push/PR, source upload
  on push to `main`, manual translation download → PR.
- Placeholder parity enforcement in `scripts/lang-audit.php`.

Remaining (needs a Crowdin token — see runbook §9):

1. Seed existing translations **before** the first download so the partial
   French/German work in git reaches Crowdin:
   `crowdin upload sources` then `crowdin upload translations`.
2. Verify the round-trip: `crowdin download` and diff.
3. Add repo secret `CROWDIN_PERSONAL_TOKEN` + repo variable
   `CROWDIN_ENABLED=true`.

### Phase 2 — DB content

Add two artisan commands, `translations:export-db` and
`translations:import-db`, that flatten the four content tables to CSV/XLSX
(one file per entity + locale column) and read them back. Upload those exports
to a **separate Crowdin folder** so content translators are not mixed with UI
translators.

Recommended order: **migrate `page_contents` CMS strings into `lang/` first**
(they already fall back to keys) — that removes the messiest table before
tackling petitions/pages/categories.

### Phase 3 — Automation & quality

- Crowdin webhooks or a scheduled GH Action to open translation PRs.
- Export approved-only; keep the TM/glossary in the loop.
- Optional Crowdin QA checks for placeholders, HTML tags, and **em dashes**
  (the repo deliberately removed em dashes — do not let autofill reintroduce
  them).
- Decide the fate of the legacy `resources/lang/` tree and `Locale::trans()`
  `messages.*` (either delete or migrate).
- Decide whether to translate emails later.

## 5. `crowdin.yml` (added)

```yaml
project_id: 936363
api_token_env: CROWDIN_PERSONAL_TOKEN
preserve_hierarchy: true

files:
  - source: /lang/en/*.php
    translation: /lang/%two_letters_code%/%original_file_name%
    update_option: update_as_unapproved
```

Notes:
- `%original_file_name%` keeps the 20-file layout per locale intact.
- DB exports (Phase 2) get their own entry, e.g.
  `source: /crowdin-content/source/*.csv` → `/crowdin-content/%two_letters_code%/*.csv`,
  stored outside `lang/`.

## 6. CI workflow (added to `.github/workflows/i18n.yml`)

- `lang-parity` — runs `php scripts/lang-audit.php` on every push/PR.
- `crowdin-upload` — on push to `main`, gated by repo variable
  `CROWDIN_ENABLED == 'true'`, uploads sources only. **Verified.**
- `crowdin-download` — on manual `workflow_dispatch`: installs deps, runs
  `crowdin download translations`, then `pint lang`, then
  `scripts/lang-audit.php`, and only then opens/updates the `l10n_crowdin` PR.
  **Verified** (PR #1 updated with formatted, validated content). Needs
  `permissions: contents: write` + `pull-requests: write` and the repo setting
  *Allow GitHub Actions to create and approve pull requests*
  (`can_approve_pull_request_reviews`).

  Running `pint` and the audit **inside** the job (rather than relying on
  PR-triggered workflows) is deliberate: PRs opened with the built-in
  `GITHUB_TOKEN` do not trigger other workflow runs, so a push/PR-triggered
  `lang-parity` would never fire on the Crowdin PR. The audit is therefore part
  of the sync job and fails the job if parity breaks.

## 7. Risks & mitigations

| Risk | Mitigation |
|---|---|
| `:name` placeholders not detected as placeholders | Pilot confirmed; add custom regex placeholder in UI; `lang-audit.php` enforces parity |
| API `add_file` flattens paths | Use Crowdin CLI (`preserve_hierarchy`), not raw API, for file layout |
| Placeholders dropped in a translation | CI `scripts/lang-audit.php` fails the build |
| HTML / apostrophes in strings | Crowdin QA check per string; review diffs in PR |
| Crowdin reformats PHP arrays → noisy diffs | Run `pint` on changed `lang/` files post-download; review PR |
| Existing translations lost on first download | Seed with `crowdin upload translations` **before** first download (done) |
| Crowdin MT/auto-translate overrides human work in exports | Review download PRs; delete stray `provider: crowdin` translations; approve human work |
| `es/pt/sv` vs `es-ES/pt-PT/sv-SE` mismatch | `%two_letters_code%` in paths (verified in pilot project data) |
| Legacy `resources/lang` duplicates confuse tooling | `crowdin.yml` scoped to `lang/` only; decide removal separately |
| Token leakage | `CROWDIN_PERSONAL_TOKEN` secret only; never write to repo config |
| CI red before secret is set | `crowdin-upload` gated by `CROWDIN_ENABLED` repo variable |

## 8. Open decisions

1. Include DB content in the same Crowdin project (separate folder) or a second
   project?
2. Delete the legacy `resources/lang/` tree, or migrate it?
3. Translate emails now or later?
4. Who reviews/approves translations in Crowdin (workflow + roles)?
5. Manual/scheduled downloads vs webhook-driven PRs?

## 9. Runbook (remaining Phase 1 steps)

1. **Create a Personal Access Token** in Crowdin (Account Settings → API →
   Tokens), scope: Projects. Store it locally, never commit it.
2. **Add the custom placeholder** in the Crowdin UI: Project Settings →
   Placeholders → Custom, regex `:[a-zA-Z_][a-zA-Z0-9_]*`.
3. **Seed the project** (one-time) — ✅ **done**. For reference, from the repo
   root. Crowdin CLI v5;
   `api_token_env: CROWDIN_PERSONAL_TOKEN` is still valid, and the CLI also
   auto-reads `$CROWDIN_PERSONAL_TOKEN` / `$HOME/.crowdin.yml` (`api_token: …`,
   chmod 600) as a credential store.

   ```sh
   export CROWDIN_PERSONAL_TOKEN=<token>
   npx @crowdin/cli@5 upload sources --dryrun        # preview, no changes
   npx @crowdin/cli@5 upload sources
   npx @crowdin/cli@5 upload translations            # brings existing fr/de/... in
   npx @crowdin/cli@5 download translations          # verify round-trip diff
   /opt/homebrew/opt/php/bin/php ./vendor/bin/pint lang
   php scripts/lang-audit.php
   ```

   `npx @crowdin/cli@5 config lint` validates `crowdin.yml` but needs the token
   resolved first (it reports `api_token is missing` from a bare shell).

4. **Enable CI**: repository secret `CROWDIN_PERSONAL_TOKEN`, repository
   variable `CROWDIN_ENABLED=true`.
5. Subsequent flow: push to `main` uploads sources; run the **i18n parity**
   workflow manually to pull translations and open a PR.
6. **Rotate the token** — the PAT used for seeding was handled during setup;
   create a fresh one and update `CROWDIN_PERSONAL_TOKEN` (and the GitHub
   secret) once CI is enabled.
