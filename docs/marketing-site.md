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
