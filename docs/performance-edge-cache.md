# Anonymous HTML edge cache

The origin renders an allowlist of public GET routes (`petitions.index`,
`petitions.byCategory`, `petition.show`, `page.show`, `faqs`,
`how-to-write-a-petition`, `donations`, `personal-data-processing`) through a
stateless path when the request allows it.

## Origin behaviour

`OptionalSession` runs first on the two listing routes. If the request has no
cookies, no Authorization header and `auth()->guest()`, it sets the request
attribute `stateless_render = true` and skips EncryptCookies,
AddQueuedCookiesToResponse, StartSession, ShareErrorsFromSession and
ValidateCsrfToken. Otherwise it runs those five around the rest of the stack,
so cookie-bearing and authenticated requests keep full session, CSRF and flash
behaviour.

`EdgeCacheHeaders` evaluates the final response on those routes. It sends
`Cache-Control: public, max-age=0, s-maxage={ttl}, stale-while-revalidate=60`
and `X-FreeCause-Edge-Cache: eligible` only when `stateless_render === true`
AND GET AND no query string AND 200 AND text/html AND zero response cookies AND
no Vary header AND the body contains no CSRF form field (`name="_token"`).
The token check is a fail-safe: any page that renders a POST form (sign forms,
contact form) bypasses even if it is added to the allowlist by mistake. The ttl
is `config('edge-cache.ttl', 600)` seconds, capped to
1..900. Every other response gets `Cache-Control: private, no-store` and
`X-FreeCause-Edge-Cache: bypass`. The marker is an internal request attribute
only, never a client-controlled header; cookies are never stripped to become
eligible.

## Why the other routes bypass

Home is excluded because `SetLocale` auto-redirects `/{locale}` (and `/`)
according to Accept-Language, answers that redirect with a `locale_redirected_to`
cookie and `Vary: Accept-Language`, and the cached variant would otherwise be
served to visitors who should have been redirected. `faqs`,
`how-to-write-a-petition`, `donations`, `personal-data-processing` and the
`page.show` slugs are safe because the locale in the path is explicit and the
auto-redirect only matches `root`/`home`.

Petition detail is cacheable for cookie-less guests. Two adaptations make that
safe and JS-free:

- The guest sign forms are replaced by a single "sign now" button that links to
  the sessionful confirm page, so the cached HTML contains no CSRF token at all
  and no-JS visitors still reach a working form.
- `recordView` guards its session calls with `hasSession()`; stateless renders
  count one view per origin render with no per-session dedupe. Combined with
  edge caching, `petitions.views` therefore approximates origin renders rather
  than user views (it is a vanity metric; `signature_count` is unaffected).

`create-petition` and the sign flows stay sessionful: their forms are the page,
and serving them without a token would break submission for visitors without
JS. Navbar authentication, remember-me cookies and toast flash data are likewise
personalized. Removing Set-Cookie after rendering would share CSRF tokens and
break native POST signing. The root, auth, OAuth, admin, confirmation, thanks,
owner and sitemap routes are excluded.

## Purge

Only `EDGE_CACHE_PURGE_ENABLED` remains. It drives `EdgeCachePurge`, which
targets canonical, queryless listing and category URLs in active locales.
List cache-version bumps already run purges for signatures, petition changes,
category translations and raw-write invalidation paths; admin page writes purge
these listings because layout metadata may change. Purges are deferred until
transaction commit and failures are isolated from user writes. The transport is
synchronous after commit, bounded to five seconds per batch of 30 URLs, and
disabled by default.

## Cloudflare rule (review before mutation)

The rule lives in `deploy/cloudflare/cache-rules.json` (shipped `enabled: false`,
covered by `tests/Feature/CloudflareCacheRuleTest.php`). It is deliberately
written for the Free plan and locale-agnostic:

- Only `eq`, `contains`, `not`, `and`, `or`, `any` — the Free plan rejects
  `matches`, `starts_with`, `ends_with` and `wildcard`, so a regex expression
  would fail to save and the whole edge benefit would never land.
- No locale list to maintain: the path shapes (`/petitions`, `/petition/`,
  `/faqs`, …) are matched without the locale prefix, and the route slugs are the
  same in every locale, so adding a locale cannot silently drop a page from the
  cache.
- Positive guards for personalized or form paths: `/user/`, `/admin`, `/sign`,
  `/create-petition`, `/download`, `/widget`.
- Origin stays authoritative: `edge_ttl.mode = bypass_by_default` means
  Cloudflare only caches responses that carry the origin's
  `Cache-Control: public, s-maxage=…`; everything else (including all
  session-bearing pages) keeps its `no-store` and is bypassed.

The MCP/API token cannot create or edit Cache Rules (405 for this
authentication scheme), so this must be applied in the Cloudflare dashboard:
paste the expression, let the dashboard validate it, and only then enable the
rule. If it rejects anything, nothing is applied — the origin is unaffected.

## Deploy order (one-way safe)

1. Deploy the origin changes first and verify on a real guest request that a
   cacheable page contains **zero** `name="_token"` and no `Set-Cookie`
   (`curl -s -D - -o body.html <url> | grep -iE 'x-freecause|set-cookie'` plus
   `grep -c 'name="_token"' body.html`).
2. Confirm the CTA target (`/petition/{slug}/{id}/sign/confirm`) is sessionful
   and not edge-eligible, and walk the no-JS flow end to end (detail → form →
   confirm → sign) — covered by
   `EdgeCacheGuestPagesTest::test_no_js_guest_can_complete_the_sign_flow`.
3. Only then enable the Cloudflare rule. Enabling it first would cache
   token-bearing HTML and produce 419s.

## Sign funnel hypothesis (measure, do not assume)

Replacing the guest inline form with a "sign now" button costs one navigation
for the highest-intent action, so the trade-off is a hypothesis:

- `data-sign-cta` carries `data-cached=1|0`; the public bundle sends a GA4
  `sign_cta_click` event with `variant: stateless|session`.
- Compare before/after in GA4: detail pageviews → `/sign/confirm` pageviews →
  `sign_cta_click` → thanks pageviews, and signatures ÷ detail views over the
  same windows.
- If conversion drops, the two one-click alternatives that keep the page
  cacheable are (a) a small uncached endpoint that hands the cached form its
  token, or (b) rendering the form as an uncached fragment. Both were evaluated
  and deferred in favour of the no-JS-safe button.

## Deprecated metric

`petitions.views` / `petition_views` are write-only in production code (no
Blade, admin, export, sitemap or API reads them — audited). After edge caching
they mean "origin renders", not user views, and stateless renders have no
per-session dedupe. Treat them as deprecated: do not surface them as "views" in
any UI, and add a client beacon before they are used as a product metric.

## Rule hygiene

Do not override the origin TTL or strip Set-Cookie, do not use a blanket
cache-everything rule, and keep the default cache key and query behaviour. A
final bypass rule for the same candidate paths should match any cookie,
Authorization or query, with action set_cache_settings and cache:false; rules
combine in order and later conflicting settings win. GET-only eligibility
expressions can interfere with URL purges, so leave method gating to the origin
and to that explicit unsafe-method bypass. Verify expression syntax with the
dashboard validation before applying.

Cloudflare references: [Cache Rules settings](https://developers.cloudflare.com/cache/how-to/cache-rules/settings/),
[rule order](https://developers.cloudflare.com/cache/how-to/cache-rules/order/),
and [default cache behavior](https://developers.cloudflare.com/cache/concepts/default-cache-behavior/).

## Rollout and rollback checks

Confirm all active locales, invalid locales and legacy redirects; first browser
language visits must retain existing redirects. Confirm guest, session,
remember-me, admin, OAuth, query, failed and non-HTML responses bypass. Confirm
new stateless pages have no token, flash, account data or Set-Cookie. Verify
MISS followed by HIT for anonymous listings, static pages and petition detail,
and bypass for personalized requests. Check sign, publish, feature, category, translation and page edits
invalidate affected pages after transaction commit; rollback transactions must
never send purge requests. Failures must not interrupt the user's write. Query
variants stay uncached.

Rollback is: disable the Cloudflare rule and purge any existing edge copies. No
deployment, push, remote mutation or credentials read is part of local work.

## Watch for one day after enabling the rule

1. **419 / CSRF errors** (Sentry `Page Expired`, or a spike in 419 responses) —
   means token-bearing HTML got cached, or a cached page's form was submitted
   without a session. Turn the rule off, purge, re-check step 1 of the deploy
   order.
2. **Origin 5xx** (Sentry) — especially around petition detail, from the
   `hasSession()` guards in `recordView`.
3. **`cf-cache-status` ratio** on the new paths: a low HIT ratio on paths that
   should be cached means the expression is not matching what you think it is
   (check `http.cookie eq ""` first — any stray cookie defeats it).
