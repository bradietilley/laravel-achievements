# Usage

## How it works

1. You store achievements as rows on the `achievements` table (via the `Achievement` model).
2. Each achievement has **criteria** (all must pass) and **events** (class names that trigger evaluation).
3. On boot, the package registers a wildcard event listener that only acts on events listed by live achievements.
4. When a matching event fires and an authenticated `EarnsAchievements` user is present, `ProcessAchievement` runs (sync or queued).
5. If every criterion passes and the user does not yet have the achievement, it is granted. If criteria fail and the achievement is `reverseable`, it is revoked.

Guests never receive achievements — evaluation is skipped when there is no authenticated user.

## Creating achievements

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

$achievement->listenToEloquent(Post::class, 'created');
$achievement->save();
```

### Achievement fields

| Field | Purpose |
| --- | --- |
| `name` | Unique display name; also used for lookups (`hasAchievement('…')`) |
| `tier` | Free-form label (e.g. `bronze`, `silver`, `gold`) |
| `criteria` | Array of `Criteria` instances (AND logic — all must pass) |
| `events` | Event class names that trigger evaluation |
| `reverseable` | If `true`, failing criteria later will revoke the achievement |
| `async` | If `true`, evaluation is queued; otherwise it runs synchronously |

An empty `criteria` array means the user is always eligible when a listened event fires.

## Listening to events

### Custom / framework events

```php
use Illuminate\Auth\Events\Authenticated;

$achievement->listenTo(Authenticated::class);
$achievement->save();
```

### Eloquent lifecycle events

```php
$achievement->listenToEloquent(Post::class, 'created', 'updated');
$achievement->save();
```

This stores events like `eloquent.created: App\Models\Post`.

You can combine both:

```php
$achievement
    ->listenTo(Authenticated::class)
    ->listenToEloquent(Post::class, 'created')
    ->save();
```

## Manual grants and checks

```php
$user->giveAchievement('Prolific Poster');
$user->hasAchievement('Prolific Poster'); // true
$user->revokeAchievement('Prolific Poster');

$user->achievements; // morph-to-many collection
```

You can also operate on the achievement model:

```php
$achievement = Achievement::findByName('Prolific Poster');
$achievement->give($user);
$achievement->revoke($user);
```

## Caching

Achievement definitions and their event list are cached for an hour. Creating, updating, or deleting an achievement clears and regenerates the cache via an observer.

If you seed achievements outside Eloquent lifecycle hooks, refresh the cache yourself:

```php
use BradieTilley\Achievements\Achievements;

Achievements::make()->regenerateCache();
```

## Async evaluation

Set `async => true` when eligibility checks are expensive (heavy relation counts, external calls, etc.). The job receives the event payload and a `CarbonImmutable $now` snapshot from dispatch time, so date/time criteria stay consistent even if the job runs later.

Configure the queue via [configuration](../configuration/README.md).

## Tips

- **Pick the right trigger event.** Date-only achievements (visit on a holiday) usually listen to `Authenticated` or a dedicated “page viewed” event — not Eloquent model events.
- **Keep sync for request-bound UX.** If the UI should show the badge immediately after an action, leave `async` false.
- **Use `reverseable` deliberately.** Seasonal or threshold achievements that should disappear when no longer valid need `reverseable => true`. Lifetime badges should be `false`.
- **All criteria are AND.** Split into separate achievements if you want OR semantics.
- **Names are unique.** Prefer stable human-readable names; lookups go by name through the cache.
- **Custom criteria must be allowlisted** in `config/achievements.php` under `criteria` before they can be deserialized from the database.
- **Ignored events.** High-volume framework events (queries, cache, queue loops, etc.) are ignored by default so the wildcard listener stays cheap.
- **Timezone.** Date/time criteria use the `$now` snapshot (app timezone at dispatch). Ensure your app timezone matches how you think about “today”.

## Next steps

- [Criteria reference](../criteria/README.md)
- [Examples](../examples/README.md)
- [Reputation](../reputation/README.md)
