# Agent Quickstart

StartupGraph is agent-native: the intended user of this API is an AI agent
working on behalf of a person. This page gets an agent connected in under a
minute.

## 1. Get a token

A human with an account runs:

```bash
php artisan api:token you@example.com --name=investing-agent
```

The `--name` becomes the provenance label (`created_via`) on everything the
agent writes.

## 2. Connect over MCP (recommended)

Hosted endpoint (Streamable HTTP):

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

Local stdio (read-only, no token needed):

```json
{
  "mcpServers": {
    "startupgraph": { "command": "php", "args": ["artisan", "mcp:serve"] }
  }
}
```

### Tools

| Tool | What it does |
|------|--------------|
| `search_companies` | Query the graph (name, category, country, funded_recent) |
| `get_company` | Full profile: funding, headcount, people |
| `get_stats` | Database statistics |
| `search_oss_projects` | Open source projects by name/language |
| `create_list` | Create (or fetch) a named list |
| `add_to_list` | Add a company to a list, with a rationale |
| `save_note` | Attach a research memo to a company |
| `create_screen` | Save a query as a screen with a stored result snapshot |
| `refresh_screen` | Re-run a screen and update its snapshot |
| `log_signal` | Push a custom event to the user's feed |
| `list_my_research` | Summarize the user's lists, screens, recent notes |

### Prompts to try

- "Find Series-A-stage dev-tools companies funded in the last 6 months and
  put the five most interesting on a list called 'Dev tools watchlist',
  with a one-line rationale each."
- "Create a screen called 'German AI companies' for category ai_ml in
  Germany, sorted by total raised."
- "Write up what you know about {company} and save it as a note."

## 3. Or use the REST API directly

Reads need no auth:

```bash
curl https://startupgraph.dev/api/companies?category=ai_ml&funded_recent=6m
```

Writes take the same bearer token:

```bash
curl -X POST https://startupgraph.dev/api/lists \
  -H "Authorization: Bearer <token>" -H "Content-Type: application/json" \
  -d '{"name": "AI infra watchlist"}'
```

Full surface: [`/openapi.yaml`](../public/openapi.yaml) and
[`/llms.txt`](../public/llms.txt).

## Semantics worth knowing

- **Everything is slug-addressed.** `get_company`/`add_to_list` take the
  company slug from search results.
- **Writes are idempotent-friendly.** `create_list` and `add_to_list` are
  find-or-create; `create_screen` upserts by name. Safe to retry.
- **Signals are the feed.** New funding rounds and big headcount changes on
  companies the user follows land there automatically; `log_signal` adds
  anything else worth surfacing.
- **Provenance is recorded.** Every write is stamped with the token name, so
  the human can always see which agent did what.
