# Criteria

Criteria decide whether a user is eligible for an achievement. Every criterion on an achievement must return `true` from `isEligible(...)`.

Built-in criteria are always allowed. Custom subclasses must be listed in `config/achievements.php` under `criteria` — see [Configuration](../configuration/README.md).

## Date and time

### `CurrentDateCriteria`

Eligible when “now” falls on a specific calendar day (year, month, and day).

```php
use BradieTilley\Achievements\Criteria\CurrentDateCriteria;

new CurrentDateCriteria('2026-03-17');
```

### `DateBetweenCriteria`

Inclusive range between two datetimes.

```php
use BradieTilley\Achievements\Criteria\DateBetweenCriteria;

new DateBetweenCriteria('2026-12-01', '2026-12-31');
```

### `DayOfWeekCriteria`

Carbon day-of-week integers: `0` = Sunday … `6` = Saturday.

```php
use BradieTilley\Achievements\Criteria\DayOfWeekCriteria;

new DayOfWeekCriteria([0, 6]); // weekends
```

### `MonthCriteria`

Calendar months `1`–`12`.

```php
use BradieTilley\Achievements\Criteria\MonthCriteria;

new MonthCriteria([12]); // December
```

### `TimeOfDayCriteria`

Time window on the current day. Supports overnight ranges (e.g. `22:00`–`06:00`).

```php
use BradieTilley\Achievements\Criteria\TimeOfDayCriteria;

new TimeOfDayCriteria('09:00', '17:00');
```

### `TimeSinceCriteria`

Compare a user datetime field against a threshold relative to now.

```php
use BradieTilley\Achievements\Criteria\TimeSinceCriteria;
use BradieTilley\Achievements\Enums\ComparisonOperator;
use Carbon\Unit;

// Account is at least 1 year old
new TimeSinceCriteria('created_at', ComparisonOperator::LessThanOrEqual, 1, Unit::Year);
```

## User fields

### `FieldHasValueCriteria`

Exact equality on a user attribute (supports dotted paths via `data_get`). Prefer `FieldIsNullCriteria` when checking nulls.

```php
use BradieTilley\Achievements\Criteria\FieldHasValueCriteria;

new FieldHasValueCriteria('plan', 'pro');
```

### `FieldCompareCriteria`

Compare a field with a `ComparisonOperator`.

```php
use BradieTilley\Achievements\Criteria\FieldCompareCriteria;
use BradieTilley\Achievements\Enums\ComparisonOperator;

new FieldCompareCriteria('login_count', ComparisonOperator::GreaterThanOrEqual, 100);
```

### `FieldInCriteria`

Value is (or is not) in a list.

```php
use BradieTilley\Achievements\Criteria\FieldInCriteria;

new FieldInCriteria('country', ['IE', 'GB']);
new FieldInCriteria('role', ['banned'], in: false);
```

### `FieldIsNullCriteria`

```php
use BradieTilley\Achievements\Criteria\FieldIsNullCriteria;

new FieldIsNullCriteria('deleted_at');           // must be null
new FieldIsNullCriteria('email_verified_at', null: false); // must not be null
```

### `HasFieldCountCriteria`

A numeric user field is greater than or equal to a threshold (e.g. a denormalized counter column).

```php
use BradieTilley\Achievements\Criteria\HasFieldCountCriteria;

new HasFieldCountCriteria('posts_count', 10);
```

## Relations

Relation criteria share an optional `$where` filter:

- Associative map: `['status' => 'published']` (equality)
- List of conditions: `[['field' => 'title', 'operator' => 'LIKE', 'value' => '%laravel%']]`

### `HasRelationCountCriteria`

```php
use BradieTilley\Achievements\Criteria\HasRelationCountCriteria;

new HasRelationCountCriteria('posts', 10);
new HasRelationCountCriteria('posts', 5, ['status' => 'published']);
```

### `HasRelationExistsCriteria`

```php
use BradieTilley\Achievements\Criteria\HasRelationExistsCriteria;

new HasRelationExistsCriteria('avatar');
new HasRelationExistsCriteria('posts', exists: false); // no posts
```

### `HasRelationSumCriteria`

```php
use BradieTilley\Achievements\Criteria\HasRelationSumCriteria;

new HasRelationSumCriteria('orders', 'total', 1000);
```

## Achievements and reputation

### `HasAchievementsCriteria`

User already holds all of the named achievements (useful for meta-achievements).

```php
use BradieTilley\Achievements\Criteria\HasAchievementsCriteria;

new HasAchievementsCriteria(['First Post', 'Prolific Poster']);
```

### `MinimumPointsCriteria`

Requires the user to implement `EarnsReputation` / `HasReputation`.

```php
use BradieTilley\Achievements\Criteria\MinimumPointsCriteria;

new MinimumPointsCriteria(100);
```

## Custom criteria

Extend `BradieTilley\Achievements\Criteria\Criteria` and implement `isEligible(...)`. Register the class in `config/achievements.php`:

```php
'criteria' => [
    App\Achievements\Criteria\MonthAndDayCriteria::class,
],
```

Public constructor properties are serialized to JSON automatically.

## Comparison operators

`BradieTilley\Achievements\Enums\ComparisonOperator`:

- `LessThan` (`<`)
- `LessThanOrEqual` (`<=`)
- `Equal` (`=`)
- `NotEqual` (`!=`)
- `GreaterThan` (`>`)
- `GreaterThanOrEqual` (`>=`)
