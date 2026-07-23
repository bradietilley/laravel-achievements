<?php

namespace BradieTilley\Achievements\Models;

use BradieTilley\Achievements\Achievements;
use BradieTilley\Achievements\AchievementsConfig;
use BradieTilley\Achievements\Casts\CriteriaSerializationCast;
use BradieTilley\Achievements\Contracts\EarnsAchievements;
use BradieTilley\Achievements\Criteria\Criteria;
use BradieTilley\Achievements\Database\Factories\AchievementFactory;
use BradieTilley\Achievements\Models\Observers\AchievementObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $name
 * @property array<int, Criteria> $criteria
 * @property array<int, string> $events
 * @property bool $reverseable
 * @property bool $async
 * @property string $tier
 */
#[Table('achievements')]
#[Fillable([
    'name',
    'criteria',
    'events',
    'reverseable',
    'async',
    'tier',
])]
#[ObservedBy(AchievementObserver::class)]
#[UseFactory(AchievementFactory::class)]
class Achievement extends Model
{
    /** @use HasFactory<AchievementFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'criteria' => CriteriaSerializationCast::class,
            'events' => 'array',
            'reverseable' => 'boolean',
            'async' => 'boolean',
        ];
    }

    /**
     * @return class-string<self>
     */
    public static function alias(): string
    {
        return AchievementsConfig::getAchievementModel();
    }

    /**
     * @return HasMany<UserAchievement, $this>
     */
    public function userAchievements(): HasMany
    {
        return $this->hasMany(UserAchievement::alias());
    }

    /**
     * Nominate Eloquent model events to listen to.
     */
    public function listenToEloquent(string|Model $model, string ...$events): static
    {
        $model = $model instanceof Model ? $model::class : $model;
        $events = array_map(
            fn (string $event): string => "eloquent.{$event}: {$model}",
            $events,
        );

        return $this->listenTo(...$events);
    }

    /**
     * Nominate any event to listen to
     */
    public function listenTo(string ...$events): static
    {
        $events = Collection::make($this->events)
            ->concat($events)
            ->unique()
            ->values()
            ->all();

        $this->events = $events;

        return $this;
    }

    /**
     * Give this achievement to the given user
     */
    public function give(Model&EarnsAchievements $user): static
    {
        Achievements::make()->giveAchievement($this, $user);

        return $this;
    }

    /**
     * Revoke this achievement from the given user, if reversable or forced.
     */
    public function revoke(Model&EarnsAchievements $user, bool $force = false): static
    {
        Achievements::make()->revokeAchievement($this, $user, $force);

        return $this;
    }

    /**
     * Get all cached achievements
     *
     * @return Collection<int, Achievement>
     */
    public static function allCached(): Collection
    {
        return Achievements::make()->getAchievements();
    }

    /**
     * Find an achievement by name
     */
    public static function findByName(string|self $achievement): Achievement
    {
        return Achievements::make()->getAchievement($achievement);
    }
}
