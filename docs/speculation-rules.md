# Speculation rules (instant navigations)

Chromium preloads likely next pages with the Speculation Rules API. The site
advertises its ruleset through an HTTP header, so the strict public CSP is
untouched (no inline script).

## Origin surface

- `routes/web.php` -> `GET /speculationrules.json` (route `speculation.rules`)
  returns the ruleset with `Content-Type: application/speculationrules+json`.
- `AppendSpeculationRulesHeader` adds `Speculation-Rules: "/speculationrules.json"`
  to GET, 200, HTML responses outside `/admin` (registered in `bootstrap/app.php`
  after `EdgeCacheHeaders`).

## Ruleset

Two lists, both `"relative_to": "document"`:

- `prefetch` — every same-origin link except `/admin/*`, `/api/*`, `/*logout*`,
  `*/download/*`, `/widgets/*`, `/widget-render/*`, `[target=_blank]`,
  `[rel~=nofollow]` and `[data-no-prerender]`. `eagerness: conservative`.
- `prerender` — the same set plus `*/create-petition*` and `*/sign*`
  exclusions. `eagerness: moderate` (hover ~200ms, Chrome pools two).

Petition detail is prerenderable again because it is edge-cached for guests
(see `docs/performance-edge-cache.md`) — a hover-triggered preload is now cheap
and correctly bypasses counting for bare prefetches. `create-petition` and the
sign pages stay excluded: they are sessionful by design.

To opt a link out later, add `data-no-prerender` to the anchor.

## Keeping stats honest

- `public/js/analytics.js` defers the GA4 `js` + `config` calls while
  `document.prerendering` is true and sends them on `prerenderingchange`, so
  prerendered pages that are never shown do not fire pageviews. GA4's own
  prerender handling then counts activations only.
- `ConditionalLogging::isSpeculative()` skips the debug log for
  `Sec-Purpose: prefetch` / `prefetch;prerender` requests.
- `PetitionController::recordView()` skips **prefetch-only** requests
  (`Sec-Purpose: prefetch` without `prerender`). Full prerenders still count:
  the page is fully rendered and very likely becomes the shown page, and if we
  skipped them the activated navigation would not be counted at all because it
  makes no second request. Trade-off documented in `SpeculativeRequestTest`.

## Verification

```
php artisan test --filter="SpeculativeRequest|SpeculationRules"
php artisan route:list --path=speculation
curl -sI https://www.freecause.com/en | grep -i speculation-rules
curl -s  https://www.freecause.com/speculationrules.json | head
```

DevTools -> Application -> Speculation Rules shows the parsed ruleset, and
Network shows `Sec-Purpose: prefetch;prerender` requests on link interaction.
