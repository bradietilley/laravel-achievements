# Laravel Achievements

Achievement and reputation system for Laravel applications.

Achievements are Eloquent models with **criteria** (eligibility rules) and **events** (when to re-evaluate). When a listened event fires for an authenticated user who earns achievements, the package checks criteria and grants or revokes the achievement accordingly.

## Documentation

- [Installation](installation/README.md)
- [Usage](usage/README.md)
- [Criteria](criteria/README.md)
- [Examples](examples/README.md)
- [Configuration](configuration/README.md)
- [Reputation](reputation/README.md)

## Requirements

- PHP 8.3+
- Laravel 13+

## Quick start

```bash
composer require bradietilley/laravel-achievements
php artisan vendor:publish --tag=achievements-config
php artisan vendor:publish --tag=achievements-migrations
php artisan migrate
```

Add the traits to your user model, then create achievements with criteria and events. See [Installation](installation/README.md) and [Examples](examples/README.md) for full walkthroughs.
