# Admin UI (BlatUI)

The admin panel is built on **BlatUI** (`anousss007/blatui`, copied components) with its
own CSS/JS bundle, separate from the public site.

## Bundles

- `resources/css/admin.css` — imports `blatui.css` and maps the FreeCause palette onto
  BlatUI tokens. Loaded by both admin layouts; public `app.css` / `app.js` are untouched.
- `resources/js/admin.js` — boots BlatUI/Alpine, then the admin behavior modules.
- `scripts/blatui.sh add|update …` — copies components into
  `resources/views/components/blatui/` and rewrites `<x-ui.*>` references to
  `<x-blatui.*>` (opening and closing tags). Always install through it; never to the
  default `components/ui` path.

## Components

- Prefer `x-blatui.*` (button, input, field, card, badge, dropdown-menu, sheet,
  alert-dialog, dialog, pagination, sonner, tooltip, …) for all new admin UI.
- `x-admin.*` components are thin adapters over the BlatUI layer: list tables, filter
  cards, page header, section panels, form fields, detail sheet, bulk toolbar,
  row-actions menu, shared confirm dialog. Use them for list/form pages instead of
  rebuilding per page.
- The shared confirm dialog (`x-admin.confirm-dialog`) is driven by
  `window.fcConfirm(message, title) → Promise<boolean>`; `confirmAction()` in
  `resources/js/admin/core.js` routes every confirmation through it. SweetAlert remains
  only for the two-field ban prompt.

## JS hooks (stable contract — keep when editing markup)

- Lists: `data-admin-bulk-config`, `.bulk-checkbox`, `[data-bulk]`, `.bulk-action`,
  `#bulk-delete` / `#bulk-unban`, `[data-admin-row-hover]`, `tr[data-detail-row]`.
- Drawer: `.fc-drawer` (sheet hook), `[data-fc-dirty]`, `data-slug-source`/`data-slug-target`,
  `data-live-validate`, `[data-admin-autosubmit]`, `.fc-filter-form` (live search).
- Payload viewer (spam/logs): `[data-payload-toggle]`, `[data-payload-copy]`.
- CKEditor islands: `textarea.fc-editor[data-ckeditor=rich]` (mounted by `editors.js`).

## CSP

Admin `script-src` allows `'unsafe-inline' 'unsafe-eval'` (Alpine needs eval). The public
CSP stays strict — never add eval there.

## Styling rules

- Do **not** use `flex!` / `block!` (important display) on elements toggled by Alpine
  `x-show`: `!important` beats Alpine's inline `display:none`. Use the plain utility.
- Admin links default to ink; the public link color is scoped out with
  `a:not([data-slot])`. Brand-colored links must opt in with `text-brand!`.
- Keep table body text on the shared 13px scale; headers are 11px uppercase muted.
