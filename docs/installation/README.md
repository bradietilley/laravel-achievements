# Installation

```bash
composer require bradietilley/laravel-achievements
```

Publish the config and migrations, then migrate:

```bash
php artisan vendor:publish --tag=achievements-config
php artisan vendor:publish --tag=achievements-migrations
php artisan migrate
```

## User model

Implement the contracts and use the traits on whichever model should earn achievements (typically `User`):

```php
use BradieTilley\Achievements\Concerns\HasAchievements;
use BradieTilley\Achievements\Concerns\HasReputation;
use BradieTilley\Achievements\Contracts\EarnsAchievements;
use BradieTilley\Achievements\Contracts\EarnsReputation;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements EarnsAchievements, EarnsReputation
{
    use HasAchievements;
    use HasReputation;
}
```

`HasReputation` is optional. Use it only if you want points alongside achievements.

## Next steps

- [Usage](../usage/README.md) — creating achievements, events, and manual grants
- [Examples](../examples/README.md) — common achievement setups
- [Configuration](../configuration/README.md) — models, queues, and custom criteria
