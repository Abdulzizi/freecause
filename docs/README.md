# Documentation

| Doc | Covers |
|---|---|
| [`../README.md`](../README.md) | Project overview, stack, layout, admin panel, i18n, production notes |
| [`../deploy/README.md`](../deploy/README.md) | Deploy/rollback runbook (Deployer 8), shared dirs, GitHub Environment setup |
| `operations.md` | Local only, not committed: server facts, operational traps, parked backlog |
| [`design-system.md`](design-system.md) | Tokens, layers, components, metrics, exceptions, `ds-lint` gate |
| [`ux-states.md`](ux-states.md) | Public UX state matrix (loading/empty/error/success), a11y gate rules, landing/redirect table |
| [`mobile.md`](mobile.md) | Mobile layer: bottom nav/sheets, breakpoints, thumb-zone actions |
| [`public-motion.md`](public-motion.md) | Public motion/animation rules and reduced-motion handling |
| [`i18n-crowdin.md`](i18n-crowdin.md) | Crowdin / i18n integration runbook |
| [`performance-edge-cache.md`](performance-edge-cache.md) | Anonymous edge-cache design; why listings bypass |
| [`speculation-rules.md`](speculation-rules.md) | Speculation Rules / prerender configuration |
| [`mail-sources.md`](mail-sources.md) | Every mail source, the product-mail switches, SMTP2GO free-tier limits |
| [`edge-waf-bots.md`](edge-waf-bots.md) | Cloudflare WAF rules, Free-plan limits, bot/crawler traffic findings |
| [`admin-ui.md`](admin-ui.md) | Admin panel: BlatUI bundles/components, JS hooks, CSP, styling rules |

Operational docs that are intentionally local-only (never committed, per
`AGENTS.md`): `AGENTS.md` and any
handover/plan files.

Root-level project docs (tracked): `README.md` (overview), `CONTRIBUTING.md`
(branch model, checks, releases), `SECURITY.md` (reporting), `CHANGELOG.md`
(release notes).
