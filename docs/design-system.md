# Design System

Status: **shipped** for the public site; admin converges through BlatUI mapping.
Source of truth: `resources/css/tokens.css`. Guard: `scripts/ds-lint.sh`.
Plan and phase breakdown: `/Users/user/.opencode/plan/design-system-remediation.md`.

## Cascade layers

`resources/css/app.css` declares the layer order and imports in this sequence:

```
@layer theme, base, legacy, components, utilities;
@import 'tailwindcss/theme.css' layer(theme);
@import 'tailwindcss/preflight.css' layer(base);
@import 'tailwindcss/utilities.css' layer(utilities);
@import './tokens.css';
@import './legacy-compat.css' layer(legacy);
```

`legacy-compat.css` (the inherited Bootstrap-derived sheet) sits **below**
`components` and `utilities`, so plain utilities always win. This is why no
view uses an `!` important suffix any more: the 4,570 overrides were only ever
compensating for the old unlayered sheet. Rules to keep it that way:

- Never import CSS outside a layer in `app.css` / `admin.css`.
- Never reach for `!` again: if a utility loses, the competing rule belongs in
  `legacy-compat` or carry-over specificity, so fix the source.
- Local `@media`/`@supports` blocks inside the legacy sheet keep working.

`resources/css/admin.css` imports `tokens.css` and maps the BlatUI variables
(`--primary`, `--radius`, `--border`, `--ring`, `--sidebar-*`, …) onto the same
tokens, so the admin panel and the public site share one palette.

## Tokens

Defined once in `resources/css/tokens.css` as `--fc-*` custom properties and
surfaced to Tailwind v4 through `@theme` (so utilities like `bg-surface`,
`text-muted`, `rounded-md`, `shadow-elev-1`, `ease-decel` exist).

| Group | Tokens |
|---|---|
| Surfaces | `bg-canvas`, `bg-surface`, `bg-surface-2`, `bg-band`, `bg-dark`, `bg-brand-soft`, `bg-success-soft`, `bg-warning-soft`, `bg-danger-soft` |
| Text | `text-ink`, `text-muted`, `text-subtle`, `text-on-dark`, `text-on-dark-muted` |
| Lines | `border-border`, `border-border-strong`, `border-brand-border` |
| Brand + semantic | `text/bg/border-brand`, `-brand-hover`, `-brand-active`, `success`, `warning`, `danger`, `info` |
| Brand exceptions | `bg-whatsapp` (`#25d366`), `bg-facebook` (`#1877f2`) |
| Focus | `--fc-focus` (2px ring, 2px offset) |
| Type | `text-xs` 12 · `text-sm` 14 · `text-base` 16 · `text-lg` 18 · `text-xl` 22 · `text-2xl` 28 · `text-3xl` 36 · `text-4xl` clamp(44–56) — each with its own line-height and tracking |
| Radius | `rounded-sm` 6 · `rounded-md` 8 · `rounded-lg` 12 · `rounded-full` |
| Elevation | `shadow-elev-1` (rest) · `shadow-elev-2` (raised) · `shadow-elev-3` (overlay) |
| Spacing | Tailwind's 4px scale, restricted to `1,2,3,4,6,8,12,16,24,32` (4–128px) |
| Motion | `--fc-dur-instant/fast/base/slow/hero` 100/150/200/300/500ms · `--fc-ease-standard/decel/accel` |
| z-index | `z-sticky` 1020 · `z-header` 1030 · `z-overlay` 1040 · `z-modal` 1050 · `z-toast` 1060 · `z-topbar` 1070 |

Rules:

- No raw hex outside `tokens.css` (the only allowlisted hex values live there,
  plus the two social brand colours as named tokens).
- No arbitrary type or spacing (`text-[13px]`, `p-[10px]`), no `rounded-[…]`,
  no `shadow-[…]`, no arbitrary colour utilities (`bg-[#…]`).
- Dark surfaces use `bg-dark` with `text-on-dark` / `text-on-dark-muted`.

## Component vocabulary

`resources/views/components/ui/*` (public site; admin uses `x-blatui.*`):

| Component | Use for |
|---|---|
| `x-ui.section` | page sections — `tone="band"` for alternating bands, `:tight` for compact heads, `:container="false"` for full-bleed |
| `x-ui.page-head` | page title + lead + breadcrumb + `#actions` slot |
| `x-ui.card` | bordered surface (`fcui-card`) |
| `x-ui.badge` | status chips — `neutral`, `brand`, `success`, `warning`, `danger`, `info` |
| `x-ui.breadcrumb` | `<nav aria-label="breadcrumb">` with items array |
| `x-ui.empty` | empty states with `#icon` / `#action` slots |
| `x-ui.pagination` | paginator window (`partials/pagination.blade.php` delegates to it) |
| `x-ui.skeleton` | loading placeholders (`text`, `title`, `:lines`) |
| `x-ui.button` | `primary`, `danger`, `secondary`, `ghost`, `sm/md/lg`, busy state |
| `x-ui.alert` | `success`, `error`, `warning`, `info` with ARIA roles |
| `x-ui.input`, `x-ui.container`, `x-ui.spinner` | form control, layout width, loading |

Behavioural classes stay: `fc-hero`, `fc-hero-item`, `fc-reveal`, `fc-stagger`,
`fc-lift`, `fc-prose`, `fc-head-band`, `fcui-*` (JS hooks), and the Bootstrap
*collapse/dropdown/tab/modal* classes the navbar and petition page rely on.

## Motion rules

- Interaction feedback ≤200ms (`--fc-dur-fast/base`); entrances may use
  `--fc-dur-slow/hero`.
- Never animate from `scale(0)`; no font-weight change on hover.
- Hover-only affordances use Tailwind's `hover:` (gated on hover-capable
  devices); every animation honours `prefers-reduced-motion`.

## Enforcement

`bash scripts/ds-lint.sh` (grep-only, no new dependency) fails on: `!` important
suffixes in `resources/views`, `resources/js/public`, `resources/js/admin`,
`app/Services/ContentRenderer.php`; arbitrary type/spacing; disallowed radius
and elevation; arbitrary colour utilities; inline-style hex; raw hex in
`app.css`, `admin.css`, `client-toast.css`.

Scope excludes `resources/views/components/blatui` (CLI-generated) and
`resources/views/emails` (needs inline styles). Exceptions can be registered in
`scripts/ds-lint-allow.txt` (one regex per line, `#` comments).

CI: advisory step in `.github/workflows/ci.yml` until the sweep is complete,
then blocking (phase P9).

## Deliberate exceptions

- `resources/css/client-toast.css` keeps `!important` where it must beat
  SweetAlert2's own stylesheet, which is injected outside the layer order.
- `[data-hide-mobile] { display: none !important }` must beat table-cell
  utilities on mobile.
- `legacy-compat.css` retains Bootstrap *behaviour* (collapse, dropdown, tabs,
  modal) and its base element styling, now safely under the utilities layer.
  Dead visual rules inside it are removed incrementally.

## Migration metrics

| Metric | Before | After |
|---|---|---|
| `!` important overrides (views) | 4,570 | 0 |
| Arbitrary font sizes | 228 | 0 |
| Arbitrary spacing values | 71 | 0 |
| Raw hex in views | 220 | 0 |
| Raw hex in `app.css` | 81 | 0 |
| Distinct radius values | 12 | 3 + pill |
| Distinct shadow levels | 8+ | 3 + none |
| Built public CSS | 192.8 kB | see `npm run build` output |
