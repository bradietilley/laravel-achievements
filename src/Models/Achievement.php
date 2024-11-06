<?php

namespace BradieTilley\Achievements\Models;

use BradieTilley\Achievements\Achievements;
use BradieTilley\Achievements\AchievementsConfig;
use BradieTilley\Achievements\Casts\CriteriaSerializationCast;
use BradieTilley\Achievements\Contracts\EarnsAchievements;
use BradieTilley\Achievements\Criteria\Criteria;
use BradieTilley\Achievements\Database\Factories\AchievementFactory;
use BradieTilley\Achievements\Models\Observers\AchievementObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * @template TAchievement of self
 *
 * @property string $name
 * @property array<int, Criteria> $criteria
 * @property array<int, string> $events
 * @property bool $reverseable
 * @property bool $async
 */
#[ObservedBy(AchievementObserver::class)]
class Achievement extends Model
{
    /** @use HasFactory<AchievementFactory> */
    use HasFactory;

    public $table = 'achievements';

    protected $guarded = [];

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
    public static function getConfiguredClass(): string
    {
        return AchievementsConfig::getAchievementModel();
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): AchievementFactory
    {
        return new AchievementFactory();
    }

    public function userAchievements(): HasMany
    {
        return $this->hasMany(UserAchievement::getConfiguredClass());
    }

    /**
     * Nominate Eloquent model events to listen to.
     */
    public function listenToEloquent(string|Model $model, string ...$events): static
    {
        $model = $model instanceof Model ? $model::class : $model;
        $events = Arr::map($events, fn (string $event) => "eloquent.{$event}: {$model}");

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
     */
    public static function allCached(): Collection
    {
        return Achievements::make()->getAchievements();
    }

    /**
     * Find an achievement by name
     *
     * @param string|static $achievement
     */
    public static function findByName(string|self $achievement): static
    {
        if ($achievement instanceof self) {
            return $achievement;
        }

        return static::where('name', $achievement)->firstOrFail();
    }
}
