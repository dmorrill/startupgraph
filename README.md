# StartupGraph

**The startup database built for AI agents.**

StartupGraph is an open-source graph of ~70,000 startups — funding rounds,
headcount over time, leadership, news, and open-source activity — designed so
that **AI agents are the native users**. Your agent queries the graph, builds
screens and lists, and writes research memos; you read the results in a native
iPhone app.

Think of it as an agent-first Mattermark: instead of a human clicking through
filter dashboards, you ask your agent —

> "Find Series A dev-tools companies that raised in the last 6 months and are
> still under 50 people, and put the interesting ones on a list."

— and the screen, the list, and the memos show up on your phone.

**This project is being rebuilt in public.** The full plan is in
[`docs/restart-plan-ios-first.md`](docs/restart-plan-ios-first.md); the
marketing/positioning draft is in [`docs/marketing-site.md`](docs/marketing-site.md).
It's also a running showcase of AI-assisted development — most of this codebase
is built with Claude.

## Architecture

Two layers, one rule:

- **The company graph is the commons.** Companies, funding rounds, investors,
  people, headcount snapshots, news, and OSS projects. Shared by everyone,
  grown by import pipelines, agents, and community contributions.
- **The research layer is yours.** Lists, screens (saved queries), notes, and
  signals are scoped to your account and written mostly by your agent through
  the API and MCP server.

```
Your agent ──MCP/API──▶ StartupGraph (Laravel + MySQL/SQLite)
                              │
                              ▼
                        iPhone app (SwiftUI) — your screens, lists, memos
```

## For agents (and their humans)

StartupGraph speaks MCP (Model Context Protocol). Today that's a local stdio
server; a hosted endpoint at `startupgraph.dev/mcp` is part of the current
build phase.

```bash
php artisan mcp:serve
```

```json
{
  "mcpServers": {
    "startupgraph": { "command": "php", "args": ["artisan", "mcp:serve"] }
  }
}
```

Read tools available now: `search_companies`, `get_company`, `get_stats`,
`search_oss_projects`. Write tools (`create_list`, `add_to_list`, `save_note`,
`create_screen`, `log_signal`) are part of Phase 1 — see the plan.

There is also a public REST API with no auth required for reads:

| Endpoint | Description |
|----------|-------------|
| `GET /api/stats` | Database statistics |
| `GET /api/search?q=` | Search companies and people |
| `GET /api/companies` | List companies (filters: `q`, `category`, `country`, `funded_recent`, `sort`, …) |
| `GET /api/companies/{slug}` | Full company profile |
| `GET /api/companies/{slug}/funding` | Funding history |
| `GET /api/companies/{slug}/people` | Leadership team |
| `GET /api/companies/{slug}/headcount` | Employee growth |
| `GET /api/people/{slug}` | Person profile |
| `GET /api/oss-projects` | Open source projects |

## Current data

- ~70,000 companies (YC, Wikipedia, SEC EDGAR, GitHub, HackerNews, and more)
- Funding rounds with amounts, investors, and source links
- 233 curated leadership profiles, headcount snapshots, OSS star tracking
- Growing — see the [50K+ milestone](https://github.com/dmorrill/startupgraph/issues/75)

## Contributing

The easiest first contribution: several importers are built and waiting on a
free API key — grab one issue from
[`good first issue`](https://github.com/dmorrill/startupgraph/issues?q=is%3Aissue+is%3Aopen+label%3A%22good+first+issue%22),
get the key, run one artisan command, and thousands of companies land in the
commons. See [CONTRIBUTING.md](CONTRIBUTING.md) for the full range: data
sources, importers, API/backend, and (soon) the iOS app.

## Local development

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

Tests: `php artisan test`

## Tech stack

- Laravel 12 · PHP 8.2+ · SQLite (dev) / MySQL (prod)
- Blade + Tailwind for the web/admin UI
- SwiftUI iPhone app (in progress — see the plan)
- MCP server for agent integration

## License

MIT
