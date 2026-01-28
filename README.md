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

### Open issues

- [#3](https://github.com/dmorrill/startupgraph/issues/3) - Backfill source URLs for funding rounds
- [#4](https://github.com/dmorrill/startupgraph/issues/4) - Add recurring job to refresh company data

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

Use StartupGraph directly from Claude Code with the MCP server:

```bash
# Add to Claude Code
claude mcp add startupgraph -- npx @startupgraph/mcp

# Or with custom API URL
claude mcp add startupgraph -- npx @startupgraph/mcp --url http://localhost:8000/api
```

### Available Tools

| Tool | Description |
|------|-------------|
| `search_companies` | Search for companies by name, industry, or location |
| `get_company` | Get detailed traction data for a specific company |
| `get_funding_history` | Get complete funding rounds with investors |
| `get_leadership` | Get company executives and team |
| `get_headcount_history` | Get employee count over time |
| `compare_companies` | Compare 2-5 companies side-by-side |
| `list_categories` | List available category filters |
| `get_stats` | Get database statistics |

Once installed, ask Claude Code questions like:
- "What's Stripe's funding history?"
- "Compare OpenAI and Anthropic"
- "Find AI companies in San Francisco"

See [mcp-server/README.md](mcp-server/README.md) for full documentation.

## Future plans

- Hosted public API at startupgraph.dev
- Community contributions to keep data fresh
- Automated data refresh from company websites

## License

MIT
