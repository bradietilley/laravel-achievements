# Achievements

Achievement and reputation system for Laravel applications.

![Static Analysis](https://github.com/bradietilley/laravel-achievements/actions/workflows/static.yml/badge.svg)
![Tests](https://github.com/bradietilley/laravel-achievements/actions/workflows/tests.yml/badge.svg)
![Laravel Version](https://img.shields.io/badge/Laravel%20Version-13.x-F9322C)
![PHP Version](https://img.shields.io/badge/PHP%20Version-%E2%89%A58.3-4F5B93)

## Documentation

Full documentation is available at [bradietilley.dev/laravel-achievements](https://bradietilley.dev/laravel-achievements).

## Installation

```bash
composer require bradietilley/laravel-achievements
```

```bash
php artisan vendor:publish --tag=achievements-config
php artisan vendor:publish --tag=achievements-migrations
php artisan migrate
```

```php
use BradieTilley\Achievements\Concerns\HasAchievements;
use BradieTilley\Achievements\Concerns\HasReputation;
use BradieTilley\Achievements\Contracts\EarnsAchievements;
use BradieTilley\Achievements\Contracts\EarnsReputation;

class User extends Authenticatable implements EarnsAchievements, EarnsReputation
{
    use HasAchievements;
    use HasReputation;
}
```

See the [documentation](https://bradietilley.dev/laravel-achievements) for usage, criteria, examples, and configuration.

## Credits

- [Bradie Tilley](https://github.com/bradietilley)
