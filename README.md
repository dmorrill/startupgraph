# StartupGraph

An open source database of startups tracking company growth, funding, and news mentions over time.

## What is this?

StartupGraph helps you keep tabs on startups by tracking:

- **Company basics** - name, website, description, founded date, location
- **Product highlights** - key products, features, and capabilities
- **Leadership** - founders, executives, and key team members with LinkedIn profiles
- **Headcount over time** - see how teams grow (or shrink)
- **Funding history** - rounds, amounts, investors, and source links
- **News mentions** - media coverage and press

## Current data

The database currently includes **107 companies** with:
- Product highlights (6 bullet points per company)
- Leadership profiles (233 people total)
- Funding rounds with source URLs
- Headcount snapshots

## Features

- Sortable and filterable company table
- Company detail pages with product info and team
- Person profile pages showing career history
- Filter by funding recency (3 months, 6 months, 1 year, etc.)
- Sort by total raised, last fundraise date, headcount
- **Export** — CSV and JSON export with current filters applied
- **Open source tracking** — GitHub OSS project discovery and star monitoring
- **Public submissions** — community submission form with admin review
- **XML Sitemap** — auto-generated at `/sitemap.xml`
- **Public REST API** — no auth required, designed for AI agents
- **MCP server** — direct integration with Claude and other AI tools

## Use cases

- **Investing** - Track growth signals and company trajectory
- **Job hunting** - Evaluate potential employers beyond LinkedIn
- **Curiosity** - Browse and discover interesting companies

## Tech stack

- Laravel 12
- SQLite (development) / MySQL/PostgreSQL (production)
- Tailwind CSS
- Blade templates

## Local development

```bash
# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Run migrations
php artisan migrate

# Seed the database
php artisan db:seed

# Start the dev server
php artisan serve
```

## Monthly maintenance

To keep the data fresh, run these tasks monthly:

### 1. Update employee headcounts

Fetch latest employee counts from LinkedIn:

```bash
php artisan headcount:fetch-linkedin --limit=107
```

This creates new HeadcountSnapshot records when counts change, building historical data for the growth charts.

### 2. Review new funding rounds

Check for recent funding announcements and add new FundingRound records. Sources:
- [Crunchbase News](https://news.crunchbase.com/)
- [TechCrunch Fundraising](https://techcrunch.com/tag/fundraising/)

### 3. Update company profiles (quarterly)

Refresh product highlights and leadership data as companies evolve.

## Status

Early development. See [Issue #1](https://github.com/dmorrill/startupgraph/issues/1) for the full vision and roadmap.

### Recent additions

- OSS project tracking with GitHub star monitoring
- Indie project tracking with Product Hunt discovery
- MCP server for AI tool integration
- Public submission form for community contributions
- Data audit command for completeness reporting

## API

StartupGraph provides a public JSON API with no authentication required. Designed to be queried by AI agents like Claude Code.

### Endpoints

| Endpoint | Description |
|----------|-------------|
| `GET /api/stats` | Database statistics |
| `GET /api/search?q=` | Search companies and people |
| `GET /api/companies` | List companies with filters |
| `GET /api/companies/{slug}` | Full company profile |
| `GET /api/companies/{slug}/funding` | Funding history |
| `GET /api/companies/{slug}/people` | Leadership team |
| `GET /api/companies/{slug}/headcount` | Employee growth |
| `GET /api/people/{slug}` | Person profile |
| `GET /api/categories` | List category filters |
| `GET /api/oss-projects` | List open source projects |
| `GET /api/oss-projects/{id}` | OSS project details |

### Query Parameters

For `/api/companies`:
- `q` - Search by name, description, city, country
- `category` - Filter by category (ai_ml, fintech, enterprise, etc.)
- `country` - Filter by country
- `funded_after` - Date filter (YYYY-MM-DD)
- `funded_before` - Date filter (YYYY-MM-DD)
- `funded_recent` - Preset filter (3m, 6m, 1y, 2y)
- `sort` - Sort field (name, founded_date, funding_rounds_sum_amount, etc.)
- `order` - Sort direction (asc, desc)
- `per_page` - Results per page (default 50, max 100)

### Example

```bash
# Get all AI/ML companies
curl "http://localhost:8000/api/companies?category=ai_ml"

# Search for a company
curl "http://localhost:8000/api/search?q=stripe"

# Get full company profile
curl "http://localhost:8000/api/companies/stripe"
```

## MCP Server

StartupGraph includes a built-in MCP (Model Context Protocol) server for direct AI tool integration:

```bash
# Run the MCP server
php artisan mcp:serve
```

Configure in your AI tool using the provided `mcp.json`:

```json
{
  "mcpServers": {
    "startupgraph": {
      "command": "php",
      "args": ["artisan", "mcp:serve"]
    }
  }
}
```

### Available Tools

| Tool | Description |
|------|-------------|
| `search_companies` | Search for companies by name, category, or country |
| `get_company` | Get detailed company info with funding and headcount |
| `get_stats` | Get database statistics |
| `search_oss_projects` | Search open source projects by name or language |

Ask your AI tool questions like:
- "What's Stripe's funding history?"
- "Find AI companies in San Francisco"
- "Show me trending open source projects"

## Future plans

- Hosted public API at startupgraph.dev
- Community contributions to keep data fresh
- Automated data refresh from company websites

## License

MIT
