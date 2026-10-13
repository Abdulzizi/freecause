# Mobile Layer

Plan: `/Users/user/.opencode/plan/mobile-revolution-plan.md`. Public site only; admin is BlatUI.

## Structure

- Breakpoint switch: **bottom nav under `md` (768px)**, desktop top nav at `md` and up
  (`navbar-expand-md`). Desktop layout is unchanged.
- `viewport-fit=cover` is set in `layouts/legacy.blade.php`; tokens in `tokens.css`:
  `--fc-safe-top/bottom/left/right`, `--fc-nav-h` 56px, `--fc-action-h` 64px, `--fc-tap-min` 48px.
- `body:has(.fcui-bottom-nav)` reserves bottom space; `main` gets extra padding when a
  `.fcui-sticky-action` is present, so nothing is covered.

## Components

| Component | Use |
|---|---|
| `x-ui.bottom-nav :items="[…]"` | global bottom navigation (rendered once in `partials/navbar.blade.php`) |
| `x-ui.bottom-sheet id="…" title="…"` | contextual list/actions (bottom), `variant="center"` for forms |
| `x-ui.action-sheet id="…" title="…" :actions="[…]"` | list of actions (sign, share, sort, language…) |
| `x-ui.sticky-action` | thumb-zone primary action; `:href`, `:label`, `:context`; hidden at `md+` |
| `x-ui.chip-row` + `.fcui-chip` | horizontal category/topic chips |
| `.fcui-icon-btn`, `.fcui-tap` | 48px icon button / tap-target helper |
| `.fcui-sheet-list__item` | 48px sheet row |

Any element can open a sheet: `data-sheet-open="<sheet-id>"`; close with `data-sheet-close`.
`resources/js/public/sheets.js` handles `<dialog>` showModal/close, Esc, backdrop, Back-button
(history push/pop), focus return, scroll lock and swipe-down dismiss. External file only — the
strict CSP forbids inline scripts.

## Rules

- Mobile-first: base styles are for small screens, add `md:` upward. Desktop must not change.
- Tap targets ≥48px (`min-h-12` or `.fcui-tap`); inputs `text-base` (16px) so iOS does not zoom.
- Forms: single column, stacked labels, 48px fields, sticky submit in the thumb zone.
- Motion: sheets 300ms ease-out, nav 200ms, reduced-motion respected, no scale-from-0, no hover-only affordances.
- Cached guests on petition detail must keep receiving **no `_token`** and no session — the sticky
  CTA links to the sessionful confirm page (`$signTarget`); never render the inline form in the
  stateless branch.
- No arbitrary values, no `!` suffixes (see `docs/design-system.md`); `ds-lint` must stay green.
