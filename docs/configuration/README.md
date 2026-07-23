# Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=achievements-config
```

The file lives at `config/achievements.php`.

## Models

Swap package models for your own subclasses:

```php
'models' => [
    'achievement' => BradieTilley\Achievements\Models\Achievement::class,
    'user_achievement' => BradieTilley\Achievements\Models\UserAchievement::class,
    'reputation' => BradieTilley\Achievements\Models\Reputation::class,
    'reputation_log' => BradieTilley\Achievements\Models\ReputationLog::class,
],
```

## Jobs and queues

```php
'jobs' => [
    'process_achievement' => BradieTilley\Achievements\Jobs\ProcessAchievement::class,
    'queue' => null,       // e.g. 'achievements'
    'connection' => null,  // e.g. 'redis'
],
```

When `queue` is set, the service provider calls `Queue::route()` for the process job so async achievements use that queue (and optional connection).

## Events

```php
'events' => [
    'achievement_granted' => BradieTilley\Achievements\Events\AchievementGranted::class,
    'achievement_revoked' => BradieTilley\Achievements\Events\AchievementRevoked::class,
],
```

Listen for these in your app to send notifications, update UI, etc.

## Custom criteria

Built-in package criteria are always allowed when deserializing JSON from the database. Any custom `Criteria` subclass you store must be allowlisted:

```php
'criteria' => [
    App\Achievements\Criteria\MonthAndDayCriteria::class,
],
```

Without this entry, loading an achievement that references the custom class will fail safely rather than instantiating an unexpected type.
