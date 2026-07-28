# StartupGraph Restart: Agent-First, iOS-First

*Decided July 2026. This document is the source of truth for the project restart —
it supersedes the original web-MVP framing in issue #1 and the README where they conflict.*

## The product idea

An **agent-first Mattermark**, used through an iPhone.

Mattermark was company profiles + screens (saved filter queries) + curated lists
for deal flow. In the agent-first version, the human never sits in front of a
filter builder. Agents (running in Elle's investing repo, talking to this backend
via MCP and the API) do the screening, researching, and monitoring. The iPhone app
is a **viewport, not a workbench**: it renders what the agents produce.

StartupGraph's role: the company graph agents query, the research store agents
write to, and the API the iOS app reads.

## Decisions made

| Decision | Choice |
|----------|--------|
| Primary client | Native iOS (SwiftUI). Blade web UI is demoted to admin/debug chrome. |
| iOS v1 scope | **Read-only.** Browse/search companies, view lists, screens, notes, signals. No editing — even "build a screen" happens by asking an agent. |
| Users | **Multi-tenant from the start**: registration + per-user API tokens. Elle is user #1; TestFlight beta users follow. A pro tier (or shutdown) stays possible later. |
| Agent research | Lives **in this backend** as first-class data (lists, screens, notes, signals). The investing repo's agents write here; the phone renders from here. |
| Dataset | Crucial, and should keep growing. The bulk import/discovery pipeline stays and gets investment (see issues #68–#75). |
| Repo | **Stays public — built in public.** The repo doubles as a showcase of AI-assisted development, and public ownership keeps the project with Elle independent of her company. Open-source framing (README, CONTRIBUTING) stays, refreshed for the new direction. |

## Architecture: global graph vs. user-scoped research

The one structural rule that keeps multi-tenancy cheap later:

**Global company graph** (shared; would be common to all tenants someday):
- `companies`, `funding_rounds`, `investors`, `people`, `headcount_snapshots`,
  `news_mentions`, `open_source_projects`
- Maintained by import pipelines and agents. No `user_id`.

**User-scoped research layer** (personal; every table carries `user_id` from day one,
even though it's always user #1 for now):
- `lists` / `list_entries` — curated collections of companies, agent- or human-created,
  with a `rationale` per entry (why this company is on the list)
- `screens` — saved queries over the graph (generalize the existing `saved_searches`),
  with agent-refreshable result snapshots
- `notes` — research memos attached to a company (markdown, agent-authored, with
  provenance: which agent, when, from what prompt/source)
- `signals` — an event feed ("raised a round", "headcount jumped 20%", "added to
  list X by agent Y") that becomes the phone's home screen

Rules of thumb:
- Never query a research-layer table without a `user_id` scope, even now.
- No global mutable state that assumes one user (config-bound preferences, etc.).
- Company-graph writes stay attributable (which pipeline/agent, when) but not user-owned.

## Interfaces

1. **Read API** — exists today (`routes/api.php`), public, no auth. Stays as-is;
   iOS uses it for browse/search. Extend with endpoints for lists, screens, notes,
   signals (these require auth since they're personal).
2. **Write API** — new. Token auth (Laravel Sanctum, one personal token). Endpoints
   for creating/updating lists, screens, notes, signals. This is what agents use.
3. **MCP server** — promoted from nice-to-have to **primary product interface**,
   and hosted: a remote MCP endpoint (Streamable HTTP) at `startupgraph.dev/mcp`
   so any agent connects with just a URL + token — not a localhost artisan
   process. Existing read tools stay; add write tools: `create_list`,
   `add_to_list`, `save_note`, `create_screen`, `refresh_screen`, `log_signal`.
4. **iOS app** — thin SwiftUI client over the API. Reference architecture:
   Groupthink's `app-iOS` (Laravel backend + native client + web sharing one
   database), borrowed selectively since this client is thinner than a typical app.

## Agent-native by design

Agents are the native users from day one; humans mostly browse what agents
produce. What that means concretely:

1. **Hosted MCP endpoint is the front door.** Connecting an agent must be
   "paste a URL, paste a token" — no cloning, no PHP, no local process.
2. **Parity rule.** Anything the iOS app can display, an agent can query;
   anything an agent can do goes through the same authed API a user's token
   uses. No UI-only features, no agent-only backdoors.
3. **Agent-legible surface.** OpenAPI spec, `llms.txt`, predictable JSON
   envelopes, stable slugs, cursor pagination, structured errors, idempotent
   writes. Docs written as copy-pasteable prompts and `mcp.json` snippets, not
   just human prose.
4. **Provenance on every write.** Which token/agent wrote it, when, and
   optionally why (the `rationale` fields). This is what makes agent-written
   research trustworthy and makes a community review queue possible later.

## Marketing site (startupgraph.dev)

Positioning: **"The startup database built for AI agents."** The site's job is
to sell the loop: *you ask your agent → the agent works through StartupGraph →
screens, lists, and memos appear on your phone.*

- Primary CTAs: **Connect your agent** (token signup + `mcp.json` snippet) and
  **Get the iPhone app** (TestFlight).
- Serve `llms.txt` and agent-oriented docs; the site itself must be as legible
  to a visiting agent as to a human.
- Build-in-public angle: the repo, this plan, and the AI-assisted development
  story are part of the pitch.
- Message architecture and copy draft: `docs/marketing-site.md`.

## Phases

### Phase 0 — Security & deploy readiness (blocking)
- [x] Audit git history for leaked secrets (#105). **Result (2026-07-27): no real
      secret was ever committed.** The only Laravel key in the entire reachable
      history is the obvious dummy `base64:testing1234…` in `.env.testing`
      (added 2026-02-18 for CI), which matches GitGuardian's Laravel-APP_KEY
      pattern — almost certainly the source of the March 2026 alerts. `.env` was
      never tracked; no other token patterns found. Caveat: this covers reachable
      history of `main`; commits pushed to since-deleted branches aren't in a
      fresh clone, so the GitGuardian alert details (in dmorrill's email) should
      be glanced at once to confirm they point at `.env.testing`.
- [ ] Dismiss the GitGuardian alerts as false positives (dmorrill, via the
      GitGuardian dashboard) after confirming the flagged file.
- [ ] Generate a fresh `APP_KEY` per environment at deploy time (standard
      practice; nothing to rotate since no environment exists yet).
- [ ] Since the repo stays public and gains real users: keep secrets exclusively
      in env vars, and consider a pre-push secret-scan hook or CI secret scan.
- [ ] Deploy the backend (#84 already scopes Laravel Cloud). The iPhone can't
      talk to a laptop; a hosted API is a v1 prerequisite.

### Phase 1 — Backend: the research layer
- [ ] Sanctum per-user token auth; registration (web auth scaffolding already
      exists); authenticated write routes.
- [ ] Migrations + models: `List`, `ListEntry`, `Note`, `Signal`; generalize
      `SavedSearch` → `Screen` with stored result snapshots. All with `user_id`.
- [ ] Write API endpoints + feature tests.
- [ ] MCP write tools wired to the same endpoints; host the MCP server as a
      remote endpoint (Streamable HTTP) at `startupgraph.dev/mcp`.
- [ ] OpenAPI spec + `llms.txt` + agent quickstart docs.
- [ ] Signals generation: emit signal rows from existing pipelines (new funding
      round detected, headcount snapshot delta, etc.).

### Phase 2 — iOS v1 (read-only)
- [ ] SwiftUI app: signals feed (home), search, company profile (funding,
      headcount chart, people, notes), lists, screens.
- [ ] Auth: sign-in with per-user API token.
- [ ] Ship via TestFlight — Elle first, then a public TestFlight beta.
- Explicitly out of scope: editing, push notifications (candidate for v1.1),
  billing/pro tier.

### Phase 2.5 — Marketing site
- [ ] Landing page at startupgraph.dev selling the agent-first loop, with the
      two CTAs (connect your agent / get the app), `llms.txt`, and docs.
      Copy draft in `docs/marketing-site.md`.

### Phase 3 — Grow the dataset (parallel, ongoing)
- [ ] Unblock importers that just need API keys: GitHub orgs (#68), Product Hunt
      (#69), OpenCorporates (#70), Companies House (#71).
- [ ] Expand Wikipedia categories (#74); track the 50K→70K+ milestone (#75).
- [ ] Recurring refresh jobs so the graph stays current (funding, headcount, OSS stars).

## Community & contributions

Building in public includes building *with* people. The mental model: **the
company graph is the commons; the research layer is yours.** Contributions grow
the shared graph; each user's lists/notes/screens stay private to them.

On-ramps, easiest first:
1. **Data imports** — issues #68–#71 are literally "get a free API key, run one
   artisan command" (labeled `good first issue` / `help wanted`), and #74 is
   adding Wikipedia categories to an existing importer. Perfect first PRs.
2. **Company submissions** — the public form + admin review flow already exists.
3. **Code** — new importers/discovery sources, API endpoints, and eventually the
   iOS app itself.
4. **Agent-mediated contributions** (the novel one, later) — outside contributors
   point their own agents at the MCP server to propose graph updates, landing in
   a review queue rather than writing directly. Fits the agent-first thesis.

Prerequisites to make this real:
- [ ] Rewrite the README — it still describes the 107-company curated MVP; it
      should sell the agent-first vision and link this plan.
- [ ] Refresh CONTRIBUTING.md for the new direction and on-ramps above.
- [ ] Decide where the iOS app code lives (this repo vs. a sibling repo) —
      affects who can contribute to it.

## Open questions

- Whether to pull in Groupthink `app-iOS` scaffolding wholesale or just crib its
  API-client/auth patterns — pending a look at that repo.
- Hosting: Laravel Cloud per #84, unless something changed.
- Push notifications for signals: v1.1, needs APNs setup.
