# Contributing to StartupGraph

StartupGraph is built in public, and contributions grow **the commons** — the
shared company graph every user's agent works from. (Each user's research
layer — lists, notes, screens — is private and not part of contributions.)

## Ways to contribute, easiest first

### 1. Unlock a data source (great first contribution)

Several importers are fully built and just need a free API key:

- [#68](https://github.com/dmorrill/startupgraph/issues/68) GitHub orgs — token with `read:org`
- [#69](https://github.com/dmorrill/startupgraph/issues/69) Product Hunt — free dev token
- [#70](https://github.com/dmorrill/startupgraph/issues/70) OpenCorporates — free API key
- [#71](https://github.com/dmorrill/startupgraph/issues/71) Companies House UK — free API key
- [#74](https://github.com/dmorrill/startupgraph/issues/74) Add Wikipedia categories to the existing importer

Each is: get key → add to `.env` → `php artisan companies:bulk-import --source=…`
→ open a PR with any importer fixes you needed along the way.

### 2. Submit companies

No dev setup needed — use the public submission form on the site. Submissions
go through admin review.

### 3. Code

- New discovery/import sources (`app/Services/Discovery`, `app/Services/BulkImport`)
- API and MCP server improvements
- The SwiftUI iOS app, once scaffolding lands (see the
  [restart plan](docs/restart-plan-ios-first.md))

### 4. Point your agent at it

The project is agent-native: an increasing amount of contribution can happen
through the MCP server. Agent-mediated contributions to the shared graph
(with review queue) are on the roadmap — see the plan.

## Setup

```bash
git clone git@github.com:dmorrill/startupgraph.git
cd startupgraph
composer install && npm install
cp .env.example .env
php artisan key:generate && php artisan migrate
php artisan db:seed
php artisan serve
```

## Testing

```bash
php artisan test
```

Please include tests with backend changes — the suite covers models, API
endpoints, and feature flows, and CI runs it on every PR.

## Workflow

Feature branches + PRs, never direct to `main`. Keep PRs focused; link the
issue you're addressing.
