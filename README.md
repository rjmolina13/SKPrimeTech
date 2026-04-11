# SKPrime – Laravel + Filament

## Quick Start (macOS)
- ./scripts/setup_macos.sh
- php artisan serve
- Visit /admin and login: admin@example.com / password

## Roles
- Super Admin: full access
- Municipal: scoped to own municipality

## Local Database
- Default: SQLite database/database.sqlite
- For MySQL/PostgreSQL, copy .env.example settings and run: php artisan migrate --force

## Packages
- filament/filament
- spatie/laravel-permission
- laravel/sanctum
- league/csv

## Testing
- php artisan test

## Notes
- The Filament dashboard shows system-wide stats and a pie chart by municipality.
