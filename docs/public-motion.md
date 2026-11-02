# Public motion & visual system

Reference for the public-site motion/visual refresh (phases P0–P5). Admin has its own system
(`docs/admin-ui.md`). WordPress magazine (`public/magazine`) and widget embeds are out of scope.

## Tokens

Defined in `resources/css/app.css` `:root`. Components and pages reference these tokens instead of
hardcoding durations, easings, radii or shadows.

| Token | Value | Used for |
| --- | --- | --- |
| `--fc-dur-instant` | 100ms | micro states |
| `--fc-dur-fast` | 150ms | hover, press, pop |
| `--fc-dur-base` | 250ms | tab panes, dialogs, accordion |
| `--fc-dur-slow` | 400ms | reveals, entrances |
| `--fc-dur-hero` | 800ms | hero choreography (once per load) |
| `--fc-ease-standard` | `cubic-bezier(0.25, 1, 0.5, 1)` | state changes |
| `--fc-ease-decel` | `cubic-bezier(0.16, 1, 0.3, 1)` | entrances |
| `--fc-ease-accel` | `cubic-bezier(0.4, 0, 1, 1)` | exits |
| `--fc-rise-sm` | 8px | small entrance offset |
| `--fc-rise-md` | 16px | reveal offset |
| `--fc-stagger` | 40ms | per-item stagger step |
| `--fc-press-scale` | 0.98 | button press |
| `--fc-hover-lift` | -3px | card hover lift |
| `--fc-hairline` | `rgba(33, 37, 41, 0.08)` | header/footer/prose separators |
| `--fc-band` | `#f4f2ee` | alternating section band on the warm page background |
| `--fc-r-sm/md/lg/pill` | 6 / 8 / 12 / 999px | radii |
| `--fc-elev-1/2/3` | shadow scale | cards, hover, overlays |
| `--color-brand` | `#cc1f1f` | accent (locked, unchanged by the refresh) |
| `--fc-link-underline-offset` | 3px | link underlines |

## Primitives

Every primitive is an enhancement: if it is missing or unsupported, the page renders complete and
readable with no hidden content.

| Primitive | Effect | Applied on | Fallback |
| --- | --- | --- | --- |
| `.fc-section` | section rhythm `padding-block: clamp(3.5rem, 8vw, 6rem)` | page sections | normal padding |
| `.fc-head-band` | compact page-head padding (20–32px top, 14–20px bottom) | static/contacts/magazine heads | normal padding |
| `#fc-main > section:first-child` | compact 20–32px top padding on every public page (home hero excluded) | all public pages | normal padding |
| `.fc-prose` | 78ch measure, paragraph/list/blockquote rhythm, hairline `hr`, `scroll-margin-top` on headings | static/legal content | plain content |
| `.fc-hero` + `.fc-hero-item` | one-shot stagger: headline → sub → CTA (0/80/160ms) | home hero only | visible immediately |
| `.fc-reveal` | scroll-driven fade + rise (`animation-timeline: view()`) | blocks below the fold | visible immediately |
| `.fc-stagger` (children) | children rise in 40ms steps, first 8 indexed | grids/lists: results, categories, blog, comments, profiles | visible immediately |
| `.fc-lift` | hover: `-3px` + elevation-2 shadow, 150ms | cards: featured, categories, blog, related, my-petitions | static card |
| `.fc-enter` | one-shot entrance on load | error pages (403/404/419/429/500/503) | visible immediately |
| `.fc-tabular` | `font-variant-numeric: tabular-nums` | counters and stat rows | proportional digits |
| `.fc-progress-reveal` | progress fill animates when scrolled into view | petition detail, signatures page | static fill |
| `.fc-share-copy--copied` | copied state: green + pop | detail share row | label swap only (JS) |
| `fcui-btn:active` | press scale `0.98` | all buttons | static |
| `.swal2-show` / `.swal2-hide` | 250ms dialog scale + fade | account confirm dialogs | instant dialog |
| Bootstrap `.collapsing` | retimed to 250ms standard easing | FAQ accordion | Bootstrap default |
| `@view-transition` | cross-document 200ms fade + shared element `view-transition-name: petition-{id}` | list → detail card/header morph | instant navigation |
| `view-transition-name: site-header` / `site-notice` / `site-footer` | persistent chrome stays put during page transitions (`animation: none` on the groups) | navbar, maintenance notice, footer | chrome fades with the page |
| `#fcui-topbar` | load progress bar, shown only when navigation is still pending after 300ms | internal link clicks | no bar on instant navigations |
| `.fcui-sticky-signbar` | fixed mobile sign CTA (no layout shift) | petition detail | inline sign buttons |
| `.fcui-input` / `.fcui-field-*` | inputs, labels, hints, error rings | all public forms | native controls |

## Rules

- Animate only `transform` and `opacity`; never `width`, `height`, `top` or `left`.
- Every primitive has a `prefers-reduced-motion: reduce` off-switch (table below).
- Scroll-driven effects are gated behind `@supports (animation-timeline: view())` **and**
  `prefers-reduced-motion: no-preference`.
- No inline scripts (strict CSP). The only inline `<style>` blocks are the View Transition opt-outs
  on `/create-petition` and the sign page; `style-src` allows `unsafe-inline`, `script-src` does not.
- Stateless listings stay cookie-free: no motion code reads cookies, storage or the network.
- JS motion budget: **0 bytes added**. The public bundle was identical to the deployed baseline when this was
  audited; no motion library was introduced.

## Reduced-motion coverage

| Animation | Off-switch |
| --- | --- |
| `fc-hero-in` | `.fc-hero .fc-hero-item { animation: none }` |
| `fc-pane-in` | `.tab-content > .tab-pane.active { animation: none }` |
| recent-activities row stagger | `.tab-pane.active .most-listing li { animation: none }` |
| `fc-reveal-in` | gated by `no-preference`; plus explicit `animation: none` |
| `fc-copied-pop` | `animation: none` |
| `fc-enter-in` | `animation: none` |
| `fc-dialog-in/out` | `animation: none` |
| `::view-transition-*` (root + named groups) | `animation: none` |
| `fc-nav-scroll` | `nav.navbar { animation: none }` |
| `fcui-progress-grow` | `.fcui-progress-fill { animation: none }` |
| button spinner `fc-spin` | `.fcui-spinner { animation: none }` |
| skeleton shimmer | `.fcui-skeleton { animation: none }` |
| `.fc-lift` hover transform / press scale | `transform: none` |
| Bootstrap collapse | `transition-duration: 0.01ms` |

## P5 audit results

- **JS**: public bundle byte-identical to production at audit time; CSS + markup only.
- **Tests**: 443 passing (1811 assertions), Pint clean.
- **Edge cache**: `GET /en/petitions` → `x-freecause-edge-cache: eligible` + `cf-cache-status: HIT`.
  HEAD requests report `bypass` by design (`EdgeCacheHeaders` requires `GET`).
- **CSP**: `AddSecurityHeaders` unchanged; no new inline scripts.
- **a11y**: brand focus rings on inputs, buttons and legacy checkboxes/radios/switches; skip link
  kept; WCAG AA target size (24px) met (buttons ≈38px).
- **Known risk**: the home hero entrance starts at `opacity: 0`; re-check LCP in the field after
  deploy (PageSpeed Insights / CrUX). No Lighthouse run was possible in the build environment.

## Post-deploy verification

```
curl -s -D - -o /dev/null https://www.freecause.com/en/petitions \
  | grep -iE '^x-freecause-edge-cache|^cf-cache-status'     # eligible, then HIT on repeat
```

Manual pass: reduced-motion OS setting (all motion off, content intact), Chrome + Safari page
transitions, list → detail shared-element morph, `/petitions?q=…` highlight, form submit loading.
