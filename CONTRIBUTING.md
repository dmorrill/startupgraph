# Contributing to StartupGraph

Thanks for your interest in contributing! Here's how to get involved.

## Quick Start

1. Fork the repo and clone locally
2. `composer install && cp .env.example .env && php artisan key:generate`
3. `php artisan migrate && php artisan db:seed`
4. `php artisan serve` → visit http://localhost:8000

## Ways to Contribute

### Submit a Company
Use the public form at `/submit` — no code required! An admin will review your submission.

### Report Issues
Open a GitHub issue for bugs, feature requests, or data corrections.

### Code Contributions
1. Create a feature branch from `main`
2. Make your changes with tests where applicable
3. Submit a PR with a clear description

### Data Contributions
Help improve data quality by:
- Verifying company information
- Adding missing funding rounds with source URLs
- Updating headcount data

## Code Style
- Follow PSR-12 for PHP
- Use Laravel conventions
- Run `php artisan test` before submitting

## License
By contributing, you agree that your contributions will be licensed under the MIT License.
