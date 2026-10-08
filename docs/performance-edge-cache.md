# Anonymous HTML edge cache

The origin renders the two listing routes (`petitions.index`,
`petitions.byCategory`) through a stateless path when the request allows it.

## Origin behaviour

`OptionalSession` runs first on the two listing routes. If the request has no
cookies, no Authorization header and `auth()->guest()`, it sets the request
attribute `stateless_render = true` and skips EncryptCookies,
AddQueuedCookiesToResponse, StartSession, ShareErrorsFromSession and
ValidateCsrfToken. Otherwise it runs those five around the rest of the stack,
so cookie-bearing and authenticated requests keep full session, CSRF and flash
behaviour.

`EdgeCacheHeaders` evaluates the final response on those routes. It sends
`Cache-Control: public, max-age=0, s-maxage={ttl}` and
`X-FreeCause-Edge-Cache: eligible` only when `stateless_render === true` AND
GET AND no query string AND 200 AND text/html AND zero response cookies AND
no Vary header. The ttl is `config('edge-cache.ttl', 600)` seconds, capped to
1..900. Every other response gets `Cache-Control: private, no-store` and
`X-FreeCause-Edge-Cache: bypass`. The marker is an internal request attribute
only, never a client-controlled header; cookies are never stripped to become
eligible.

## Why the other routes bypass

Home is excluded because even `/{locale}` can redirect according to
Accept-Language, `locale_redirected_to`, user agent and prefetch headers.
Petition detail is excluded because guest sign forms contain session CSRF
tokens, errors and old input, and recordView uses session deduplication. Navbar
authentication, remember-me cookies and toast flash data are also personalized.
Removing Set-Cookie after rendering would share CSRF tokens and break native POST
signing. The root, auth, OAuth, admin, confirmation, thanks, owner and sitemap
routes are excluded.

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

Cache only queryless, cookie-less, Authorization-less listing GETs on the
existing host and the 14 supported locale paths:

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
new stateless pages have no token, flash, account data or Set-Cookie. Verify
MISS followed by HIT for anonymous listings and bypass for personalized
requests. Check sign, publish, feature, category, translation and page edits
invalidate affected pages after transaction commit; rollback transactions must
never send purge requests. Failures must not interrupt the user's write. Query
variants stay uncached.

Rollback is: disable the Cloudflare rule and purge any existing edge copies. No
deployment, push, remote mutation or credentials read is part of local work.
