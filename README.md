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

- Laravel 11
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

## Status

Early development. See [Issue #1](https://github.com/dmorrill/startupgraph/issues/1) for the full vision and roadmap.

### Open issues

- [#3](https://github.com/dmorrill/startupgraph/issues/3) - Backfill source URLs for funding rounds
- [#4](https://github.com/dmorrill/startupgraph/issues/4) - Add recurring job to refresh company data

## Future plans

- Public API
- MCP server for AI tool integration
- Community contributions to keep data fresh
- Automated data refresh from company websites

## License

MIT
