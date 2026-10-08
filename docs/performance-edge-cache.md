# Anonymous HTML edge cache: not implemented

The origin never made listings cacheable. `EdgeCacheHeaders` unconditionally
sends `Cache-Control: private, no-store` with `X-FreeCause-Edge-Cache: bypass`
for the candidate listing routes (`petitions.index`, `petitions.byCategory`).
The on-request `edge_cache_stateless_render` marker and the `EDGE_CACHE_ENABLED`
/ `EDGE_CACHE_TTL` origin flags were removed as unreachable scaffolding: no
renderer ever set the marker, so the eligibility branch could not run.

Only `EDGE_CACHE_PURGE_ENABLED` remains. It drives `EdgeCachePurge`, which
targets canonical, queryless listing and category URLs in active locales.
List cache-version bumps cover signatures, petition changes and raw-write
invalidation paths; category translations also bump the list version. Admin page
writes purge these listings because layout metadata may change. Purges are
deferred until transaction commit and failures are isolated from user writes.
The transport is synchronous after commit, bounded to five seconds per batch of
30 URLs, and disabled by default. Implement queued, coalesced purges before any
future activation to avoid adding write latency.

## Why listings bypass

Laravel's web middleware runs EncryptCookies, AddQueuedCookiesToResponse,
StartSession, ShareErrorsFromSession, ValidateCsrfToken, then SubstituteBindings.
SetLocale subsequently stores the locale in that session. The global
EdgeCacheHeaders middleware evaluates the final response, including cookies,
without deleting or rewriting them, so it always marks the candidate listing
routes `private, no-store`.

Home is excluded because even `/{locale}` can redirect according to
Accept-Language, `locale_redirected_to`, user agent and prefetch headers.
Petition detail is excluded because guest sign forms contain session CSRF
tokens, errors and old input, and recordView uses session deduplication. Navbar
authentication, remember-me cookies and toast flash data are also personalized.
Removing Set-Cookie after rendering would share CSRF tokens and break native POST
signing. Skipping StartSession would break these consumers. The root, auth,
OAuth, admin, confirmation, thanks, owner and sitemap routes are excluded.

## Prerequisites for a future activation

Implement and separately verify a stateless list/category renderer before adding
any cache marker back. It must avoid starting or attaching any session, reading
authentication or flash data, generating CSRF tokens or queuing cookies. Requests
with any Cookie or Authorization header must use the normal web stack. Keep
existing native form POST CSRF validation intact. Supporting detail later needs
a private session/CSRF bootstrap or private form endpoint plus separate view
tracking, with explicit review of the behavior change and JavaScript failure
fallback. Home requires preserving its browser-language redirect behavior before
any cache lookup can serve HTML.

A reintroduced origin gate would additionally require GET, no query, 200
text/html, no Vary, no response cookies and no token markup, with TTL capped at
900 seconds and the marker kept as an internal server attribute, never a
client-controlled header.

## Proposed Cloudflare rule (review before mutation)

Use the existing host and the 14 supported locale paths. Do not enable this
record unless a stateless renderer is deployed and verified:

```json
{
  "description": "FreeCause verified stateless listings only",
  "expression": "(http.host eq \"www.freecause.com\" and http.request.uri.query eq \"\" and http.cookie eq \"\" and not any(http.request.headers[\"authorization\"][*] ne \"\") and http.request.uri.path matches r\"^/(da|de|el|en|es|fr|it|nl|pl|pt|ro|ru|sv|tr)/petitions(/category-[^/]+-[0-9]+)?$\")",
  "action": "set_cache_settings",
  "action_parameters": {
    "cache": true,
    "edge_ttl": { "mode": "bypass_by_default" },
    "browser_ttl": { "mode": "respect_origin" }
  },
  "enabled": false
}
```

Cloudflare regex availability depends on the zone plan; if unavailable, generate
explicit exact listing paths and category prefixes for each active locale and
keep origin route-name checks as the final gate. Verify expression syntax with
Cloudflare validation before applying. Do not override origin TTL or strip
Set-Cookie. Do not use a blanket cache-everything rule. Keep the default cache
key and query behavior. A final bypass rule for the same candidate paths should
match any cookie, Authorization, query, or method outside GET/HEAD, with action
set_cache_settings and cache:false. Rules combine in order; later conflicting
settings win. GET-only eligibility expressions can interfere with URL purges,
so leave method gating to the origin and explicit unsafe-method bypass.

Cloudflare references: [Cache Rules settings](https://developers.cloudflare.com/cache/how-to/cache-rules/settings/),
[rule order](https://developers.cloudflare.com/cache/how-to/cache-rules/order/),
and [default cache behavior](https://developers.cloudflare.com/cache/concepts/default-cache-behavior/).

## Rollout and rollback checks

Confirm all active locales, invalid locales and legacy redirects; first browser
language visits must retain existing redirects. Confirm guest, session,
remember-me, admin, OAuth, query, failed and non-HTML responses bypass. Confirm
new stateless pages have no token, flash, account data or Set-Cookie. Apply only
after explicit rollout authorization, then verify MISS followed by HIT for
anonymous listings and bypass for personalized requests. Check sign, publish,
feature, category, translation and page edits invalidate affected pages after
transaction commit; rollback transactions must never send purge requests.
Failures must not interrupt the user's write. Query variants stay uncached.

Rollback is: disable the Cloudflare rule and purge any existing edge copies. No
deployment, push, remote mutation or credentials read is part of local work.
