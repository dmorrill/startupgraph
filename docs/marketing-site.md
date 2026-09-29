# startupgraph.dev — Marketing Site Draft

*Message architecture and copy draft for the agent-first positioning.
Companion to `docs/restart-plan-ios-first.md`.*

## Positioning

**The startup database built for AI agents.**

One sentence: StartupGraph is a 70,000-company startup graph your AI agent can
query, research, and organize for you — with an iPhone app that shows you what
your agent found.

The category move: Mattermark/Crunchbase/PitchBook sell dashboards and filter
builders to humans. StartupGraph assumes you have an agent, and sells the agent
a workspace. The human product is the *output*: screens, lists, and research
memos on your phone.

## Audiences, in priority order

1. **Agent-equipped operators** — people already running Claude Code, Cursor,
   or custom agents for investing/research/job hunting. They connect in one
   minute and get value the same day. The beachhead.
2. **iPhone-first browsers** — TestFlight users who want the feed/profiles even
   before wiring up an agent. The app must be good alone, but the site should
   always be nudging toward "now connect your agent."
3. **Contributors** — devs and data folks who grow the commons (see
   CONTRIBUTING). The build-in-public story recruits them.

## Competitive landscape: Exa and Monid

*Researched September 2026.* All three products are built for AI agents to
use, but they sit at different layers. Exa is a search engine over the whole
web, Monid is a marketplace where agents pay per call for other companies'
tools, and StartupGraph is a structured dataset about startups plus a
workspace where an agent keeps its research.

| | **StartupGraph** | **Exa.ai** | **Monid** |
|---|---|---|---|
| **Core thing** | A structured graph of startups: companies, funding rounds, investors, people, headcount over time, news, open-source projects | Its own web crawl and index with embedding-based search. Websets builds verified lists of companies or people from a single prompt. | A router that lets agents find, inspect and run 200+ paid data endpoints (1,800+ connectable tools) from one balance |
| **Scope** | Startups only | The whole web | Any tool (social data, search, ecommerce, lead generation, blockchain…) |
| **Data model** | Relational with a fixed schema (`Company`, `FundingRound`, `HeadcountSnapshot`, `Person`, `Investor`…) | Documents and web pages. Websets adds criteria-matched entities with relevance scores. | None of its own. It passes through whatever each tool returns. |
| **Where data comes from** | Import pipelines (YC, Wikipedia, SEC EDGAR, GitHub, HN, TechCrunch funding news, Product Hunt, Companies House) plus community contributions | Live crawling | Third-party providers |
| **Keeps history** | Yes. Headcount snapshots, funding timelines, OSS star history. | No. It shows the web as it is now. | No |
| **Keeps the agent's own work** | Yes. The research layer stores lists, screens (saved queries), notes and signals per user. | Websets are saved lists, but meant for prospecting rather than an ongoing research workspace | No |
| **What the human sees** | A native iPhone app that shows what the agent found | A web dashboard | Nothing. It's pure infrastructure. |
| **Business model** | Open source (MIT); REST reads need no key | Paid API, usage-based ($2.2B valuation, 400K+ developers) | Pay per call, markup on a pooled balance (pre-seed, $2.1M) |

### Where the real differences are

1. **Depth vs. breadth.** Exa can answer "find Series A dev-tools companies
   under 50 people" today by crawling the web and checking results against
   your criteria. It works out that answer from scratch on every query, from
   whatever pages exist. StartupGraph keeps it as structured rows, e.g.
   `funding_rounds.round_type`, `headcount_snapshots` over time, and
   `company_person.is_current`. That makes queries cheap, repeatable and
   comparable over time ("who grew headcount 40% since March?"). Exa can't
   answer that kind of time-series question, because the web doesn't keep old
   snapshots in a queryable form.
2. **We store the agent's work; they don't.** The write tools (`create_list`,
   `add_to_list`, `save_note`, `create_screen`, `log_signal`) are the part
   that sets us apart. Exa and Monid answer calls and keep nothing.
   StartupGraph is where the agent's research lives between sessions, and the
   iPhone app displays it — "sells the agent a workspace."
3. **Monid is a sales channel, not a competitor.** Monid doesn't have its own
   data; it resells other providers' endpoints. If StartupGraph offered a paid
   or rate-limited tier, being one of Monid's endpoints could reach agents that
   never set up our MCP server directly. Exa is closer to a real competitor,
   but also a possible data source: Websets or Exa's company search could feed
   the discovery importers (`DiscoverCompanies`, `BulkImportCompanies`) to fill
   gaps in the graph.
4. **Weaknesses to be honest about.**
   - **Coverage and freshness:** ~70K companies, and some funding rounds still
     have no source URL (issue #3). Exa's index is far larger and updates live.
   - **Verification:** Websets checks every result against your criteria and
     gives a relevance score. Our quality depends on the importers plus
     `AuditCompanyData`, and we don't give agents a per-field confidence or
     source score.
   - **Where agents connect:** the hosted MCP endpoint is still being built,
     while Exa and Monid are already one-line connections in Claude, Cursor
     and others.

### Positioning in one sentence

Exa searches the web and Monid lets agents buy tools, while StartupGraph is a
structured startup dataset that tracks changes over time, with a research
workspace your agent keeps on your behalf. Its advantages are the historical
data and the saved lists and notes, not breadth.

Two practical follow-ups: use Exa as a discovery and enrichment source feeding
the commons, and list StartupGraph on Monid as a distribution channel.

Sources: [Exa](https://exa.ai/) ·
[Exa Search](https://exa.ai/products/search) ·
[Exa Websets](https://exa.ai/websets) ·
[Exa pricing](https://exa.ai/pricing) ·
[Monid docs](https://docs.monid.ai/) ·
[Monid raises $2.1M (Dealroom)](https://dealroom.co/news/148133-monid-raises-2-1m-to-let-ai-agents-buy-tools-on-demand/) ·
[Monid on Product Hunt](https://www.producthunt.com/products/monid)

## The core loop (hero demo)

> You: "Find me Series A dev-tools companies that raised in the last 6 months
> and are still under 50 people."
>
> Your agent → StartupGraph MCP: `create_screen`, queries the graph, saves the
> screen, attaches a memo on the three most interesting.
>
> Your phone: the screen and memos are just *there*.

Show this as an animation or three-panel sequence: chat → tool calls → phone.

## Page structure

1. **Hero** — headline + the loop demo + two CTAs.
   - CTA 1: **Connect your agent** → signup → token → copy-paste `mcp.json`.
   - CTA 2: **Get the iPhone app** → TestFlight.
2. **"Your agent already knows how to use this"** — the MCP tool list rendered
   as documentation, plus a copy-paste config block:
   ```json
   {
     "mcpServers": {
       "startupgraph": {
         "url": "https://startupgraph.dev/mcp",
         "headers": { "Authorization": "Bearer <your-token>" }
       }
     }
   }
   ```
   And three example prompts to try immediately.
3. **The graph** — live stats (companies, funding rounds, people, snapshots),
   honest about coverage. Growing in public.
4. **Your research layer** — lists, screens, notes, signals; private to you,
   written mostly by your agent, readable on your phone.
5. **Built in public** — link the repo, the restart plan, the AI-assisted
   development story. "Watch it being built" is a feature.
6. **Footer** — API docs, `llms.txt`, GitHub, contribute.

## Headline candidates

- "The startup database built for AI agents."
- "Your agent's favorite startup database."
- "Deal flow, researched by your agent."
- "70,000 startups. One MCP endpoint. Your agent does the rest."

Subhead draft: *Track funding, headcount, and momentum across 70,000+
startups. Your AI agent screens and researches; you read the results on your
iPhone.*

## Agent-legibility requirements (the site itself)

- Serve `/llms.txt` summarizing the product, API, and MCP endpoint.
- Docs pages in clean markdown-ish HTML — an agent landing on any page should
  be able to get itself connected without a human reading anything.
- OpenAPI spec linked prominently; stable URLs.

## Implementation notes

- Cheapest v1: a Blade landing route in this app (it's deployed anyway) —
  no separate site infra. Static generator only if design outgrows that.
- Copy the live-stats section from the existing `/api/stats` endpoint.
- Domain: startupgraph.dev (assumed owned — verify).

## Open questions

- Free tier limits for "connect your agent" (rate limits per token)?
- Does signup exist on the web first (yes — Laravel auth already there) with
  token issuance in the profile page?
- Screenshots/video of the iOS app needed before TestFlight CTA goes live.
