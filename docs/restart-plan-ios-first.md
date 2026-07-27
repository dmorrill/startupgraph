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
| Users | **Single-user personal app** (Elle) for now. Eventually a product — build so multi-tenancy is *possible later*, not *supported now*. |
| Agent research | Lives **in this backend** as first-class data (lists, screens, notes, signals). The investing repo's agents write here; the phone renders from here. |
| Dataset | Crucial, and should keep growing. The bulk import/discovery pipeline stays and gets investment (see issues #68–#75). |
| Repo | **Private, personal project** — no longer open source. Elle flips visibility in GitHub settings; open-source framing (README, LICENSE, CONTRIBUTING, public submissions) gets cleaned up as part of the restart. |

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
3. **MCP server** — promoted from nice-to-have to **primary product interface**.
   Existing read tools stay; add write tools: `create_list`, `add_to_list`,
   `save_note`, `create_screen`, `refresh_screen`, `log_signal`.
4. **iOS app** — thin SwiftUI client over the API. Reference architecture:
   Groupthink's `app-iOS` (Laravel backend + native client + web sharing one
   database), borrowed selectively since this client is thinner than a typical app.

## Phases

### Phase 0 — Security & deploy readiness (blocking)
- [ ] Make the repo private (Elle, via GitHub settings) — decided; resolves the
      public-exposure half of #105. Check for public forks when flipping, since
      forks keep the history.
- [ ] Rotate the `APP_KEY` when setting up the deployed environment (never reuse
      the leaked one). History purge is optional once the repo is private.
- [ ] Deploy the backend (#84 already scopes Laravel Cloud). The iPhone can't
      talk to a laptop; a hosted API is a v1 prerequisite.

### Phase 1 — Backend: the research layer
- [ ] Sanctum token auth; authenticated write routes.
- [ ] Migrations + models: `List`, `ListEntry`, `Note`, `Signal`; generalize
      `SavedSearch` → `Screen` with stored result snapshots. All with `user_id`.
- [ ] Write API endpoints + feature tests.
- [ ] MCP write tools wired to the same endpoints.
- [ ] Signals generation: emit signal rows from existing pipelines (new funding
      round detected, headcount snapshot delta, etc.).

### Phase 2 — iOS v1 (read-only)
- [ ] SwiftUI app: signals feed (home), search, company profile (funding,
      headcount chart, people, notes), lists, screens.
- [ ] Auth: single stored token.
- [ ] Ship to Elle's phone via TestFlight.
- Explicitly out of scope: editing, push notifications (candidate for v1.1),
  multi-user anything.

### Phase 3 — Grow the dataset (parallel, ongoing)
- [ ] Unblock importers that just need API keys: GitHub orgs (#68), Product Hunt
      (#69), OpenCorporates (#70), Companies House (#71).
- [ ] Expand Wikipedia categories (#74); track the 50K→70K+ milestone (#75).
- [ ] Recurring refresh jobs so the graph stays current (funding, headcount, OSS stars).

## Open questions

- Whether to pull in Groupthink `app-iOS` scaffolding wholesale or just crib its
  API-client/auth patterns — pending a look at that repo.
- Hosting: Laravel Cloud per #84, unless something changed.
- Push notifications for signals: v1.1, needs APNs setup.
