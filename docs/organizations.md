# Organizations: repository findings and delivery plan

Status: discovery only; no organization application code, migrations or production data have been changed. Reference: [MVP specification v6](organizations-spec-v6.md), supplied by the project owner on 2026-11-07. This replaces the v5 follower/update scope.

Reviewed baseline: `8896f1a6ba9b7d83dd51a4100dd25c40b3d62930` (`main`, cover upload limits). No existing organization implementation was found in that baseline.

## Repository conventions

- Laravel **12.69.2**, PHP **8.3.36** in the Composer platform, MySQL, Eloquent and Blade. Frontend: Vite, Tailwind 4, Alpine; public design tokens and components already exist. Admin uses the separate BlatUI/Turbo bundle and `x-admin.*` adapters described in `docs/admin-ui.md`.
- Applicable `CONTRIBUTING.md` requires categorized branches, explicit-path Pint, design-system lint, language parity and relevant PHPUnit/Playwright checks. It refers to a local-only `AGENTS.md`, but no such file was available in this checkout or the workspace ancestors inspected; no missing instructions have been invented.
- Public/owner routes use `/{locale}`, `setLocale`, `lroute()` and the existing canonical-host helpers. New specific paths must precede the generic static-page route. Private application/management pages need no-cache/noindex and normal authenticated CSRF-protected forms. Existing public stateless/session and edge-cache behavior must be respected.
- Personal login uses `web`; administration uses the separate `admin` guard over the same `User` model. `admin_can()` and `permission:module,action` consult the existing role/permission matrix; system roles already have full access. Add an organization module, not an account-type column or a new login system.
- Validation belongs in FormRequests or existing request validators. Existing petition ownership checks are inline controller/request rules, not an existing `PetitionPolicy` class. Organization authorization should be explicit and isolated without changing those existing checks.
- ISO country validation already exists in `App\Support\Countries`. Existing foreign IDs use unsigned bigint-compatible IDs. Status/type values can follow the current validated-string/constant conventions.
- Tests use PHPUnit 11.5, factories, SQLite/`RefreshDatabase`, mail/storage fakes, and Playwright with accessibility/visual coverage. File-upload admin forms use `data-turbo="false"`. Pint must not run indiscriminately through the WordPress vendor tree.
- Public UI text lives in `lang/`. Provide en/fr/it organization copy; the other active locales can explicitly load the English organization file so the existing parity audit remains valid. Organization JSON tagline/description needs the specified current-locale → English → first nonempty fallback, rather than blindly copying relational petition translations.

## Petitions, signatures and categories

- `petitions.user_id` remains the personal author. Account deletion currently preserves petitions by nulling that relationship; adding an organization must not transfer authorship, exports or edit permissions.
- Public eligibility is `Petition::visible()`: `status = published` and `is_active = 1`. Draft/held/inactive content cannot enter organization public grids or totals.
- The content editor is owner-only and becomes unavailable after another person's confirmed, non-spam signature. Later outcome management must distinguish author authorization from that content-editability restriction: recording a result must not unlock petition text.
- `PetitionSigning` owns guest/authenticated signing and verification. Guest verification follows the current configurable email-verification rules. It prevents guests from impersonating existing verified accounts and uses generic responses. `markVerified()` confirms only eligible pending, non-spam signatures in a transaction.
- **Selected counter strategy:** reuse `petitions.signature_count`. `PetitionStats` updates counts transactionally, invalidates caches, recalculates after moderation, and has a scheduled reconciliation command using `confirmed = 1 AND is_spam = 0`. Organization totals will sum counters from currently public linked petitions. No raw-signature aggregation on public requests and no additional organization total-cache column is planned unless a concrete reliability issue is found.
- Existing `categories`/`category_translations` remain separate and untouched. The 20 closed causes in v6 will have their own idempotent seed and unique pivot pairs; no historical reclassification.

## Uploads, links, audit and email

- Intervention Image **3.11.9**, Laravel integration and GD are already installed. Existing uploads have a 40-megapixel input guard and use public storage/variant helpers. Organization processing must explicitly re-encode, strip metadata and discard animation; global image configuration currently does **not** strip metadata, so reusing its defaults unchanged would be wrong.
- Use the v6 limits (logo 2 MB/400×400; cover 3 MB/1500×500), vertical focal choices and deterministic missing-image presentation. Save replacement files before committing references, and remove old/unreferenced files after a successful change. No uploaded SVG or new crop/preview interface.
- Public website, donation and social values must be parsed/validated without fetching their destinations. Social domain checks use exact/dot-boundary matching, not substrings. Donation changes clear approval. The IT tax-code block follows v6's uppercase alphanumeric/max-16 definition; validation is not a claim of 5×1000 eligibility.
- `AdminAuditLog` and `AppLog` exist, but generic application logs are pruned after 30 days. A small organization event history is justified for state changes, original declarations/terms actor and owner reassignment. Private references/review notes must also be kept out of generic request-input logs visible to unrelated log permissions. Tokens must never enter audit payloads.
- Existing mail uses localized `MailText`, editable templates, database queues and quota gates; explicit `afterCommit()` is required because the queue connection does not enable it globally. Existing contact routing uses `email_to_staff` with the configured sender as fallback. Organization support/admin addresses must be confirmed/configured before launch, not invented in UI.
- Current quota middleware may drop nontransactional messages and defer transactional ones. Phase 3 must select the proper behavior for each organization transactional message. No new provider, purchase or follower-delivery system is part of v6 phases 1–3.

## Analytics, SEO and performance

- Existing GA events remain consent-gated. The first-party aggregate page-load counter creates no visitor identifiers and stores only fixed aggregate dimensions. It currently classifies `/org/...` as `other`, so phase 3 adoption work must explicitly reuse/extend that mechanism or existing GA page-path reports; it must not invent another visitor-tracking system or adoption-report page.
- The sitemap controller already produces a cached index, pages, categories and chunked public-petition files with canonical locale alternates. Phase 3 can add a verified-only organization sitemap without changing existing eligibility.
- Phase 1 includes profile title/description, canonical URL, OpenGraph/Twitter sharing image fallback and native-share/copy behavior. Organization sitemap, badge, outcomes and conditional homepage cards remain phase 3.
- Measure the v6 mobile Lighthouse targets (warm LCP <1.0 s; cold <2.5 s) and report device/network/cache assumptions and actual results. Do not declare targets passed without measurement. Live WhatsApp/LinkedIn/Facebook preview checks need an eligible real verified URL; do not create a fictional production organization to obtain one.

## Phase boundaries and publication

1. **Phase 1:** organizations, causes, `cause_organization`, minimal event history; application/admin/owner management; verified public profile; founder recognition; image processing; donation approval; IT tax-code block; share/report actions and metadata. Empty campaigns until integration. Omit directory/follow/outcome controls that are not functional yet.
2. **Phase 2, only after explicit phase-1 approval:** directory, nullable petition organization FK, `cause_petition`, attribution, attach/detach and consent/revocation fields. Use opaque random hashed revocation tokens, signed confirmation GET and mutation POST; do not reuse the old user-preference unsubscribe route blindly. Resolve token reissuance/resend handling before implementing that flow.
3. **Phase 3, only after explicit phase-2 approval:** outcomes, transactional messages, badge, sitemap, conditional homepage section and adoption instrumentation. Do not infer historical victories or backfill historical consent.
4. **Phase 4 is excluded:** no followers, follow buttons, follower tables, organization updates, mailing-list import/export or update sending in these PRs.

One PR per implementation phase. This draft PR begins phase 1 with the specification and findings; it is not a completed phase. The project owner explicitly authorized work on the live site in chat after v5; that direct authorization remains recorded separately from the generic no-production line retained in v6. A merge into `main` automatically deploys. Do not merge an incomplete phase merely to synchronize GitHub.

Real applications remain closed until owner-approved terms/privacy text and the required transactional email readiness are in place. Development/test terms and sample organizations stay local/test-only. No fake acceptance timestamps, fictional production profiles, public disabled “coming soon” controls or premature phase-4 schema.

## Decision required before dependent phase-1 schema work

**Owner account deletion:** the current account deletion flow removes the WordPress identity before deleting the personal User row. The new sole-owner foreign key must not accidentally block the second step or cascade-delete an organization.

Proposed default, awaiting owner decision: preserve the organization and its history, suspend it, clear the deleted owner's reference and require administrator reassignment/review before restoring public access. This requires a nullable owner reference for that exceptional state. Alternative: require explicit reassignment before account deletion, with validation before any WordPress cleanup. Do not implement either silently.

Other documented assumptions: preserve the first verification timestamp for identity locking; restore previously verified suspended organizations only after admin review; profile edits remain moderated, with explicit preapproval for donation destinations. No database change is needed for future follower delivery in this MVP.
