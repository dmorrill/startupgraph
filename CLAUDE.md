# StartupGraph - Claude Code Guidelines

## Workflow

**Always use feature branches and PRs:**
1. Create a feature branch from `main` (e.g., `feature/add-search`, `fix/funding-display`)
2. Make commits to the feature branch
3. Push and create a PR for review
4. Never commit directly to `main`

## Project structure

```
app/
├── Http/Controllers/
│   ├── CompanyController.php    # Company list and detail pages
│   └── PersonController.php     # Person profile pages
├── Models/
│   ├── Company.php              # Main company model
│   ├── Person.php               # People/executives model
│   ├── FundingRound.php         # Funding history
│   ├── HeadcountSnapshot.php    # Employee count over time
│   └── NewsMention.php          # Press/news coverage
database/
├── migrations/                   # Schema definitions
├── seeders/
│   ├── DatabaseSeeder.php       # Main seeder (runs all)
│   ├── CompanySeeder.php        # Base company data (107 companies)
│   ├── CompanyProfileSeeder.php # Product highlights + people (8 companies)
│   ├── ProductHighlightsSeeder.php  # Product highlights (99 companies)
│   ├── PeopleSeeder.php         # Executives (99 companies)
│   └── FundingRoundSeeder.php   # Funding history
resources/views/
├── layouts/app.blade.php        # Main layout with nav
├── companies/
│   ├── index.blade.php          # Company list/table
│   └── show.blade.php           # Company detail page
└── people/
    └── show.blade.php           # Person profile page
```

## Key models and relationships

**Company**
- `hasMany`: FundingRound, HeadcountSnapshot, NewsMention
- `hasOne`: latestFundingRound (using `latestOfMany`)
- `belongsToMany`: Person (pivot: company_person with role, is_current)
- `product_highlights`: JSON array of bullet points

**Person**
- `belongsToMany`: Company (pivot: company_person)
- Linked via slug for URL-friendly routes

**FundingRound**
- `source_url`: Link to news article announcing the round

## Database

- **Development**: SQLite (`database/database.sqlite`)
- **Production**: MySQL or PostgreSQL

## Common commands

```bash
# Run migrations
php artisan migrate

# Fresh migration (drops all tables)
php artisan migrate:fresh

# Seed all data
php artisan db:seed

# Seed specific seeder
php artisan db:seed --class=ProductHighlightsSeeder

# Start dev server
php artisan serve

# Tinker (REPL)
php artisan tinker
```

## Git worktree setup (optional)

You can use a git worktree to run a separate dev server for testing:

```bash
# Create a worktree (one-time setup)
git worktree add ../startupgraph-test main

# After pushing changes to main, sync the worktree
cd ../startupgraph-test
git pull origin main
php artisan db:seed --class=ProductHighlightsSeeder  # Run any new seeders
```

## Open issues to be aware of

- **#3**: Many funding rounds need source_url backfilled
- **#4**: Need recurring job to auto-refresh company data from websites

## Data notes

- 107 companies seeded with product highlights and leadership
- 233 people with roles and LinkedIn URLs
- Product highlights are 6 bullet points per company
- People can be marked as `is_current: false` for former executives
