# Reputation

Optional points system that sits alongside achievements. Use `HasReputation` / `EarnsReputation` on your user model (see [Installation](../installation/README.md)).

## Awarding points

```php
$user->giveReputation(10, 'Completed onboarding');
$user->reputation->points; // 10

$user->giveReputation(-5, 'Spam penalty');
```

Points are stored on a morph `reputations` row (not on the users table), with a log of changes in `reputation_logs`.

## Achievements from points

Use `MinimumPointsCriteria` so reputation thresholds unlock achievements. See the [Centurion example](../examples/README.md#reputation-threshold).

## Tips

- Call `giveReputation` from domain actions (orders, moderation, onboarding) rather than controllers sprinkled everywhere.
- After changing points, fire an event your reputation-based achievements listen to — or listen to Eloquent updates on the reputation model.
- Pair with `reverseable` achievements if badges should drop when points fall below a threshold.
