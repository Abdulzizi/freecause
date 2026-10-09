# Edge security & bot traffic (Cloudflare)

Zone `freecause.com` (account "Freecausecom@gmail.com's Account"), Cloudflare
**Free** plan. No code lives here — this is edge configuration + the bot-traffic
findings that drove it.

## Custom WAF rules (Free caps at 5 — repurpose, never add a 6th)

Order matters: skips first, then challenges, then blocks.

| # | Action | Rule |
|---|---|---|
| 1 | Skip | `/up` bypasses Super Bot Fight Mode (GitHub smoke test) |
| 2 | Skip | verified crawlers bypass the ruleset on profile pages (`/user/` + locale variants) |
| 3 | Managed Challenge | all profile-page traffic |
| 4 | Block | AI/scraper UAs: `Lightpanda`, `ExaSearchBot`, `SemrushBot`, `Sogou`, `Applebot` |
| 5 | Block | **scanner + spam probes** (added 2026-10-09) |

Rule 5 (Free-legal expression — no `matches`, no `starts_with`):

```
(http.request.uri.path contains "/wp-" and not http.request.uri.path contains "/magazine/") or
(http.request.uri.path contains "/cgi-bin") or
(lower(http.request.uri.path) eq "/info.php") or
(lower(http.request.uri.path) eq "/test.php") or
(http.request.uri.path contains "/__") or
(http.request.uri.path contains "@fs/") or
(http.request.uri.path contains "/api/fs") or
(http.request.uri.path contains "/api/console") or
(http.request.uri.path contains "/api/system") or
(http.request.uri.path contains "/v1/graphql") or
(lower(http.request.uri.path) contains ".env") or
(http.referer contains "washsolutions.com")
```

The `/magazine/` exclusion keeps the client's WordPress tree (`/magazine/wp-admin`,
`/magazine/wp-cron.php`) out of the block.

Rule 4 replaced a dead `cf.threat_score >= 25` rule (0 events ever — threat score
is inert on Free). Custom-rule expressions on Free reject `matches` and
`starts_with` ("Business plan or WAF Advanced required") — see traps below.

## Managing rules

- **MCP/API token can list + delete custom rules but cannot create** — create
  returns `405 Method not allowed for this authentication scheme`. Add or edit
  rules in the Cloudflare dashboard: Security → WAF → Custom rules.
- Read-only from the agent side: `cloudflare_waf_list_custom_rules`,
  `cloudflare_security_events`.

## Bot traffic profile (7 days, Oct 2–9 2026, nginx `access.log`)

~4.77M requests: 2.72M `200`, 1.23M `302`, 756K `301`, 174K `404`, 28K `499`,
11K `405`, 7.7K `503`.

- **Applebot ~1.26M** (genuine `17.166.x`; spoofed non-Apple share <10K). It is
  allowed because rule 2 whitelists verified crawlers on profile paths. `robots.txt`
  Disallow for Applebot added 2026-10-07 (`46db1e79`) — expect a multi-day decline.
- ExaSearchBot 362K, Lightpanda 201K, SemrushBot 190K, bingbot 68K, plus Baidu /
  Google-Extended / MJ12.
- **404s are 100% scanner probes**: `.env` hunts, `wp-cron.php`, `/api/fs/*`,
  `/@fs/*` (k8s secrets / AWS creds), `info.php`. No user-facing broken links —
  "fixing" this app-side is wasted work; it's an edge job (rule 5).
- **Referrer-spam campaign**: `43.104.x` with `Referer: washsolutions.com` →
  150K+ hits to `/NRA_script.js`, `/airmiles-clone_script.js`. Now blocked by rule 5.
- **503s**: 6.2K on Oct 8, concentrated 14:00–15:00 = deploy maintenance window
  plus crawler retries. Deploy recipe fix `c02a8117` targets lifting maintenance
  earlier.

## Verify

Dashboard → Security → WAF → Custom rules (per-rule event counts / CSR), or the
two read-only MCP calls above. Rule 5 events accumulate only after deploy.
