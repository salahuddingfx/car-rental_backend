# Contributing

Thank you for considering contributing to Apex Ride!

## Development Setup

```bash
# Clone the repo
git clone https://github.com/salahuddingfx/car-rental_backend.git
cd car-rental_backend

# Install dependencies
composer install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate --force
php artisan db:seed

# Run development server
php artisan serve
```

## Code Style

- Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standard
- Run `./vendor/bin/pint` before committing
- Use strict types: `declare(strict_types=1)`

## Commit Messages

```
feat: add new feature
fix: bug fix
docs: documentation changes
style: formatting, no code change
refactor: code restructuring
test: adding tests
chore: maintenance tasks
```

## Pull Request Process

1. Fork the repository
2. Create a feature branch (`git checkout -b feat/amazing-feature`)
3. Write tests for new functionality
4. Run `./vendor/bin/pint` and `php artisan test`
5. Commit with descriptive message
6. Push to your fork and open a PR
7. Wait for CI checks and review

## Project Rules

- No WebSocket/Pusher/Soketi (shared hosting)
- No API-based payment send money/cashout (manual verification only)
- No Google Maps API (use OpenStreetMap/Leaflet)
- All users treated as untrusted (Zero Trust)
- Sync driver for email (no queue worker)
- Bengali/English mixed comments acceptable

## Questions?

Open a GitHub issue or email salauddinkaderappy@gmail.com
