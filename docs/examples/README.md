# Examples

Common achievement setups. Every example assumes your user model uses `HasAchievements` (and `HasReputation` where points are involved). After creating achievements via Eloquent, the observer refreshes the cache automatically.

## Visit the site on Saint Patrick's Day

Grant a badge to anyone who authenticates on 17 March 2026.

```php
use BradieTilley\Achievements\Criteria\CurrentDateCriteria;
use BradieTilley\Achievements\Models\Achievement;
use Illuminate\Auth\Events\Authenticated;

$achievement = Achievement::create([
    'name' => "Saint Patrick's Day Visitor",
    'tier' => 'green',
    'reverseable' => false,
    'async' => false,
    'criteria' => [
        new CurrentDateCriteria('2026-03-17'),
    ],
    'events' => [],
]);

$achievement->listenTo(Authenticated::class);
$achievement->save();
```

**How it gets assigned automatically**

1. The achievement listens to Laravel's `Authenticated` event.
2. When a user logs in (or otherwise becomes authenticated) on 2026-03-17, the package evaluates criteria.
3. `CurrentDateCriteria` passes because “now” is that calendar day.
4. The user is granted the achievement if they do not already have it.

`reverseable` is `false` so the badge stays after St Patrick's Day ends. Set it to `true` if the badge should only exist while the date criteria still matches (it would be revoked on the next evaluation after the day ends).

> `CurrentDateCriteria` matches a specific year. For a recurring annual holiday every 17 March, write a small custom criterion (e.g. `month === 3 && day === 17`) and [allowlist it](../configuration/README.md#custom-criteria).

## Weekend warrior

Eligible when the user signs in on Saturday or Sunday.

```php
use BradieTilley\Achievements\Criteria\DayOfWeekCriteria;
use BradieTilley\Achievements\Models\Achievement;
use Illuminate\Auth\Events\Authenticated;

$achievement = Achievement::create([
    'name' => 'Weekend Warrior',
    'tier' => 'bronze',
    'reverseable' => true,
    'async' => false,
    'criteria' => [
        new DayOfWeekCriteria([6, 0]), // Saturday, Sunday
    ],
    'events' => [],
]);

$achievement->listenTo(Authenticated::class)->save();
```

## Early bird (before 7am)

```php
use BradieTilley\Achievements\Criteria\TimeOfDayCriteria;
use BradieTilley\Achievements\Models\Achievement;
use Illuminate\Auth\Events\Authenticated;

$achievement = Achievement::create([
    'name' => 'Early Bird',
    'tier' => 'bronze',
    'reverseable' => false,
    'async' => false,
    'criteria' => [
        new TimeOfDayCriteria('00:00', '06:59:59'),
    ],
    'events' => [],
]);

$achievement->listenTo(Authenticated::class)->save();
```

## Prolific poster (10 posts)

```php
use BradieTilley\Achievements\Criteria\HasRelationCountCriteria;
use BradieTilley\Achievements\Models\Achievement;
use App\Models\Post;

$achievement = Achievement::create([
    'name' => 'Prolific Poster',
    'tier' => 'bronze',
    'reverseable' => true,
    'async' => false,
    'criteria' => [
        new HasRelationCountCriteria('posts', 10),
    ],
    'events' => [],
]);

$achievement->listenToEloquent(Post::class, 'created', 'deleted')->save();
```

Listening to `deleted` matters when `reverseable` is true so counts that drop below the threshold can revoke the badge.

### Published posts only

```php
new HasRelationCountCriteria('posts', 10, ['status' => 'published']);
```

## First contribution

Grant on the first related model, with no reverse.

```php
use BradieTilley\Achievements\Criteria\HasRelationExistsCriteria;
use BradieTilley\Achievements\Models\Achievement;
use App\Models\Post;

$achievement = Achievement::create([
    'name' => 'First Contribution',
    'tier' => 'bronze',
    'reverseable' => false,
    'async' => false,
    'criteria' => [
        new HasRelationExistsCriteria('posts'),
    ],
    'events' => [],
]);

$achievement->listenToEloquent(Post::class, 'created')->save();
```

## Spender (sum of order totals)

```php
use BradieTilley\Achievements\Criteria\HasRelationSumCriteria;
use BradieTilley\Achievements\Models\Achievement;
use App\Models\Order;

$achievement = Achievement::create([
    'name' => 'Big Spender',
    'tier' => 'gold',
    'reverseable' => true,
    'async' => true, // sum queries can be heavier
    'criteria' => [
        new HasRelationSumCriteria('orders', 'total', 1000),
    ],
    'events' => [],
]);

$achievement->listenToEloquent(Order::class, 'created', 'updated', 'deleted')->save();
```

## Account anniversary (member for 1 year)

```php
use BradieTilley\Achievements\Criteria\TimeSinceCriteria;
use BradieTilley\Achievements\Enums\ComparisonOperator;
use BradieTilley\Achievements\Models\Achievement;
use Carbon\Unit;
use Illuminate\Auth\Events\Authenticated;

$achievement = Achievement::create([
    'name' => 'One Year Club',
    'tier' => 'silver',
    'reverseable' => false,
    'async' => false,
    'criteria' => [
        new TimeSinceCriteria('created_at', ComparisonOperator::LessThanOrEqual, 1, Unit::Year),
    ],
    'events' => [],
]);

$achievement->listenTo(Authenticated::class)->save();
```

## Reputation threshold

```php
use BradieTilley\Achievements\Criteria\MinimumPointsCriteria;
use BradieTilley\Achievements\Models\Achievement;
use BradieTilley\Achievements\Models\Reputation;

$achievement = Achievement::create([
    'name' => 'Centurion',
    'tier' => 'silver',
    'reverseable' => true,
    'async' => false,
    'criteria' => [
        new MinimumPointsCriteria(100),
    ],
    'events' => [],
]);

// Re-check whenever reputation rows change — or fire a custom event after giveReputation()
$achievement->listenToEloquent(Reputation::class, 'updated')->save();
```

Prefer a dedicated domain event after awarding points if you do not want to listen to the reputation model directly.

## Meta-achievement (collect others)

```php
use BradieTilley\Achievements\Criteria\HasAchievementsCriteria;
use BradieTilley\Achievements\Models\Achievement;
use BradieTilley\Achievements\Events\AchievementGranted;

$achievement = Achievement::create([
    'name' => 'Completionist',
    'tier' => 'platinum',
    'reverseable' => false,
    'async' => false,
    'criteria' => [
        new HasAchievementsCriteria([
            'First Contribution',
            'Prolific Poster',
            'Centurion',
        ]),
    ],
    'events' => [],
]);

$achievement->listenTo(AchievementGranted::class)->save();
```

## Combining criteria (AND)

Visit on Christmas Day **and** have at least 50 reputation points:

```php
use BradieTilley\Achievements\Criteria\CurrentDateCriteria;
use BradieTilley\Achievements\Criteria\MinimumPointsCriteria;
use BradieTilley\Achievements\Models\Achievement;
use Illuminate\Auth\Events\Authenticated;

$achievement = Achievement::create([
    'name' => 'Festive Veteran',
    'tier' => 'gold',
    'reverseable' => false,
    'async' => false,
    'criteria' => [
        new CurrentDateCriteria('2026-12-25'),
        new MinimumPointsCriteria(50),
    ],
    'events' => [],
]);

$achievement->listenTo(Authenticated::class)->save();
```

## Manual-only achievement

No events and no criteria — grant from admin UI or code only:

```php
use BradieTilley\Achievements\Models\Achievement;

$achievement = Achievement::create([
    'name' => 'Staff Pick',
    'tier' => 'gold',
    'reverseable' => true,
    'async' => false,
    'criteria' => [],
    'events' => [],
]);

$user->giveAchievement('Staff Pick');
```
