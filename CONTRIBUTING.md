# Contributing to StartupGraph

## Setup

```bash
git clone git@github.com:dmorrill/startupgraph.git
cd startupgraph
composer install && npm install
cp .env.example .env
php artisan key:generate && php artisan migrate
php artisan serve && npm run dev
```

## Testing

```bash
php artisan test
```

## Data Sources

StartupGraph imports company data from multiple sources:
- Wikipedia categories
- GitHub organizations
- Product Hunt (API key required)
- OpenCorporates (API key required)
- Companies House UK (API key required)
