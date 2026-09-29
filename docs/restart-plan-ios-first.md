# StartupGraph Restart: Agent-First, iOS-First

*Decided July 2026; roadmap updated September 2026 (Phase 1.5). This document is the source of truth for the project restart —
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

## Lessons from Exa and Monid (September 2026)

*Added after the competitive review in `docs/marketing-site.md`.* Exa (web
search API for agents) and Monid (pay-per-call tool router for agents) both
make **the agent the one that onboards**: the first working call takes one
step, inside a tool the developer already uses, and costs nothing up front.
We had the right pieces (hosted MCP, `llms.txt`, OpenAPI, quickstart) but the
front door still ran through a server admin. Principles we're adopting:

1. **Value before signup.** Exa's hosted MCP works with no key (rate-limited);
   sign-in only unlocks higher limits and more tools. For us: the graph is
   already a public commons over REST, so read tools on the hosted MCP should
   be keyless too. Writes need an account — and that's the natural signup
   prompt.
2. **Sign-in happens in the browser, not the terminal.** Exa supports OAuth on
   the MCP URL; Monid has OAuth for apps. No one should need to see or paste a
   token to connect a mainstream MCP client. A self-serve token page is the
   fallback for everything else.
3. **Teach the agent, not just the developer.** Monid's entire onboarding is
   "set up https://monid.ai/SKILL.md". A skill file can teach our *workflow*
   (screen → shortlist with rationale → memo → signal), which a tool list
   alone can't.
4. **Small default tool surface.** Exa exposes two tools by default, the rest
   opt-in. Fewer, sharper tools = less context and better tool choice.
5. **Every surface does the same things.** Monid ships MCP, skill, CLI and HTTP over one
   capability set. We already have the parity rule; extend it to guests
   (same read tools, keyed or not).
6. **Be where agents already are.** One-click installs and directory listings
   (Claude connectors/plugins, Cursor, VS Code, the MCP registry) do more for
   adoption than docs. Monid itself is a directory we can list in.
7. **Publish the limits.** Exa prints a price per endpoint and a free monthly
   allowance. We're free, but we should still publish rate limits and return
   them in headers and structured errors so agents can plan.

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

### Phase 1 — Backend: the research layer ✅ (shipped in #106)
- [x] Sanctum per-user token auth; registration (web auth scaffolding already
      exists); authenticated write routes. *Gap: tokens can only be issued by
      a server admin via `php artisan api:token` — fixed in Phase 1.5, M1.*
- [x] Migrations + models: `List`, `ListEntry`, `Note`, `Signal`; generalize
      `SavedSearch` → `Screen` with stored result snapshots. All with `user_id`.
- [x] Write API endpoints + feature tests.
- [x] MCP write tools wired to the same endpoints; host the MCP server as a
      remote endpoint (Streamable HTTP) at `startupgraph.dev/mcp`.
- [x] OpenAPI spec + `llms.txt` + agent quickstart docs.
- [x] Signals generation: emit signal rows from existing pipelines (funding
      round and headcount-delta observers).

### Phase 1.5 — Zero-friction agent onboarding

Goal: **a stranger goes from "paste one line into my agent" to their first
list on their phone without touching a terminal or a token.** Applies the
Exa/Monid lessons above. Each checkbox below is sized as one issue / one PR;
IDs (e.g. `M2.1`) are for cross-referencing in issue titles.

**Dependencies at a glance**

```
Phase 0 deploy (#84) + domain (#107) ──┐
                                       ├──▶ M5 Distribution
M1 Self-serve tokens ──▶ M3 OAuth ─────┤
M2 Keyless MCP reads ──────────────────┤
M4 Agent onboarding kit ───────────────┘
```

M1, M2 and M4 have no dependencies on each other and can be built in parallel
now (locally, before deploy). M3 builds on M1. M5 needs a live deploy.

#### M1 — Self-serve API tokens
*Done when: a registered user creates, names and revokes a token in the web
UI, and no public doc mentions `php artisan api:token`.*
- [ ] **M1.1 Token management page.** Profile → "Agent tokens": create (name
      becomes the `created_via` label), list with last-used time, revoke.
      Plaintext shown once. Sanctum already backs this; reuse
      `IssueApiToken`'s naming logic. Feature tests for create/revoke/scoping.
- [ ] **M1.2 Docs pass.** Replace the artisan instruction in `public/llms.txt`,
      `docs/agent-quickstart.md` and `README.md` with the sign-up → token page
      flow. Keep `api:token` documented only for self-hosters.
- [ ] **M1.3 Post-signup "connect your agent" screen.** After registration,
      show the token plus ready-to-paste `mcp.json` / Claude Code
      (`claude mcp add …`) snippets with the token filled in.

#### M2 — Keyless hosted MCP for reads
*Done when: `startupgraph.dev/mcp` with no `Authorization` header lists and
runs the read tools, rate-limited per IP; write tools return a structured
"sign in to save this" error.*
- [ ] **M2.1 Optional auth on `/mcp`.** Route currently requires
      `auth:sanctum` (`bootstrap/app.php`). Resolve the user if a token is
      present, otherwise continue as guest. `McpToolService::tools()` /
      `execute()` already accept a null user (the stdio server relies on it) —
      verify guest mode exposes only read tools.
- [ ] **M2.2 Guest rate limiter.** Separate `mcp-guest` limiter (e.g.
      30/min per IP) from the authenticated 120/min `api` limiter; return
      `X-RateLimit-*` headers and a JSON-RPC error carrying `retry_after` and
      a signup URL when exceeded.
- [ ] **M2.3 Auth-required error for write tools.** Guests calling
      `create_list` etc. get a structured error with a human-readable
      message the agent can relay ("Create a free account at … to save
      lists") — the upgrade prompt, not a 401.
- [ ] **M2.4 Tests + docs.** Feature tests for guest list/call/limit/write-
      refusal; update quickstart and `llms.txt` to lead with the keyless URL.

#### M3 — OAuth sign-in for MCP clients
*Done when: adding `https://startupgraph.dev/mcp` in Claude (or another MCP
client that supports auth) opens a browser login and the agent can write,
with no token pasted.*
- [ ] **M3.1 Spike: pick the OAuth stack.** Evaluate Laravel Passport vs. the
      `laravel/mcp` package's OAuth support against the MCP authorization spec
      (OAuth 2.1 + PKCE, protected-resource metadata, dynamic client
      registration). Output: short decision note in this doc.
- [ ] **M3.2 Authorization server + discovery.** Implement per M3.1:
      `/.well-known/oauth-protected-resource`, authorization-server metadata,
      dynamic client registration, consent screen. `/mcp` returns a
      `WWW-Authenticate` challenge only when a write tool needs it (keeps M2
      keyless reads working).
- [ ] **M3.3 Provenance from OAuth clients.** Registered client name →
      `created_via`, so research written through OAuth is attributed just like
      token-written research. Tokens from M1 keep working.
- [ ] **M3.4 Connected-apps UI.** List and revoke OAuth grants alongside
      tokens on the M1 page.

#### M4 — Agent onboarding kit
*Done when: "set up https://startupgraph.dev/SKILL.md" is enough for an agent
to connect and run the hero demo from `docs/marketing-site.md`.*
- [ ] **M4.1 `SKILL.md`.** Served from `public/`. Teaches connection (keyless
      first, then sign-in), the tool set, and the research routine: build a
      screen → shortlist onto a list with a rationale per entry → write memos
      → log signals. Includes the three example prompts.
- [ ] **M4.2 Tool groups.** Default hosted tool list = reads + the core write
      loop; opt-in groups via query param (e.g. `/mcp?tools=all` or
      `?tools=read,lists`). Audit every tool description for clarity and
      consistent argument names.
- [ ] **M4.3 Structured, self-describing errors.** One error envelope across
      REST and MCP (code, message, hint, docs URL); validation errors name the
      bad argument and valid values (e.g. category keys).
- [ ] **M4.4 Limits page.** Publish rate limits (guest vs. signed-in) and the
      free-forever promise for the commons in docs and `llms.txt`.

#### M5 — Distribution (after deploy)
*Done when: StartupGraph can be installed from at least three directories
without hand-editing JSON.*
- [ ] **M5.1 Official MCP registry** listing (`server.json`) for the hosted
      endpoint.
- [ ] **M5.2 Claude**: submit to the connector directory and/or publish a
      Claude Code plugin bundling the MCP config + `SKILL.md`.
- [ ] **M5.3 Cursor + VS Code**: one-click install links on the site and in
      the README; Cursor marketplace submission.
- [ ] **M5.4 Monid listing**: list the read endpoints on Monid as an extra
      channel (free or nominal price); track calls arriving via Monid.
- [ ] **M5.5 Marketing site CTA**: "Connect your agent" becomes the one-line
      skill/URL plus install buttons (feeds Phase 2.5).

#### Later — usage before pricing
Not scheduled; revisit once M1–M5 ship. Per-token/per-client usage metering
(which tools, how often, guest vs. signed-in) so any future pro tier or
credits model (Exa-style free monthly allowance) is based on real usage.

### Phase 2 — iOS v1 (read-only)
- [x] Decide where the app lives: `ios/` in this repo (monorepo — one PR flow,
      contributors see everything, fits build-in-public).
- [x] SwiftUI scaffold (`ios/`, XcodeGen project): sign-in with server URL +
      token (Keychain), signals feed (home), screens, lists with rationales,
      search, company profile with headcount chart (Swift Charts) and the
      user's notes. Not yet compiled — needs a Mac/Xcode pass (#108 covers
      TestFlight setup).
- [ ] First build + fix pass in Xcode; app icon; TestFlight (Elle first,
      then public beta — #108).
- Explicitly out of scope: editing, push notifications (candidate for v1.1),
  billing/pro tier.

### Phase 2.5 — Marketing site
- [ ] Landing page at startupgraph.dev selling the agent-first loop, with the
      two CTAs (connect your agent / get the app), `llms.txt`, and docs.
      Copy draft in `docs/marketing-site.md`. The "connect your agent" CTA
      is the one-line skill/URL from Phase 1.5 (M4.1, M5.5).

### Phase 3 — Grow the dataset (parallel, ongoing)
- [ ] Unblock importers that just need API keys: GitHub orgs (#68), Product Hunt
      (#69), OpenCorporates (#70), Companies House (#71).
- [ ] Expand Wikipedia categories (#74); track the 50K→70K+ milestone (#75).
- [ ] Recurring refresh jobs so the graph stays current (funding, headcount, OSS stars).
- [ ] Spike: Exa (Search / Websets) as a discovery and enrichment source —
      cost per 1K companies, fit with `DiscoverCompanies`/`BulkImportCompanies`,
      and terms of use for storing results in an open commons (cf. #72).

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

- Hosting: Laravel Cloud per #84, unless something changed. Domain: #107.
- Push notifications for signals: v1.1, needs APNs setup.
- Groupthink `app-iOS` reference: the scaffold was built fresh (thin client
  didn't warrant importing a full architecture); still worth a comparison
  pass for auth/API-client patterns if the repo gets added to a session.
