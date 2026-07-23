# Changelog

All notable changes to this project will be documented in this file.

## [1.0.0] - 2026-07-20

### Added
- Laravel 13 model attributes (`#[Table]`, `#[Fillable]`, `#[UseFactory]`) and job attributes (`#[Tries]`, `#[Timeout]`, `#[DeleteWhenMissingModels]`)
- Optional `Queue::route()` configuration via `achievements.jobs.queue` / `connection`
- Criteria class allowlist (`achievements.criteria`) for safer JSON deserialization
- `ComparisonOperator` enum for `TimeSinceCriteria`
- Unique index on reputation morph columns
- Cache TTL extension via `Cache::touch()` on achievement cache hits
- `FieldCompareCriteria`, `FieldInCriteria`, and `FieldIsNullCriteria` for richer user-field checks
- `DateBetweenCriteria`, `DayOfWeekCriteria`, `MonthCriteria`, and `TimeOfDayCriteria` for calendar/time windows
- `HasRelationSumCriteria` for summing a relation column against a threshold
- Shared `RelationCriteria` base with optional `$where` filters (associative maps or field/operator/value lists, including `LIKE`) on relation count, exists, and sum criteria

### Changed
- **Breaking:** Requires Laravel 13+ and PHP 8.3+
- **Breaking:** Achievement events cache key renamed to `bradietilley_achievements.events`
- **Breaking:** `TimeSinceCriteria` now requires `ComparisonOperator` and `Carbon\Unit` (string operators/units removed)
- Achievement cache is lazy-loaded on first read instead of regenerating on every boot
- Async `ProcessAchievement` jobs now receive the event payload
- **Breaking:** Async `ProcessAchievement` jobs snapshot `CarbonImmutable $now` at dispatch; `Criteria::isEligible()` requires `$now` and date/time criteria use it instead of `Carbon::now()`
- Unique constraint conflict detection uses `UniqueConstraintViolationException`
- `Achievement::findByName()` resolves through the configured model alias and cache
- `HasRelationCountCriteria` and `HasRelationExistsCriteria` extend shared `RelationCriteria` and accept an optional `$where` filter

### Fixed
- Stale per-achievement name cache inconsistency by resolving names from the achievements collection

## [0.1.0] - 2026-07-19

### Added
- Initial release with achievement criteria, reputation tracking, and event-driven grants
- Support for Laravel 12 and Laravel 13
- Support for PHP 8.2+

### Fixed
- Added missing `async` column to the achievements migration
- Fixed `HasAchievementsCriteria` eligibility checks to use achievement IDs
- Fixed `Criteria::toArray()` to return named property keys
- Made unique-constraint handling work across SQLite, MySQL, and PostgreSQL
- Prevented the wildcard event listener from booting models during migrations (Laravel 13)

### Changed
- Persist achievement criteria as JSON class payloads instead of PHP `serialize()`
- Align achievement cache writes and reads on a shared TTL
- Updated CI to test Laravel 12/13 across supported PHP versions
- Dropped Laravel 11 support
