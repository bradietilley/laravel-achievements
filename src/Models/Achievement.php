<?php

namespace BradieTilley\Achievements\Models;

use BradieTilley\Achievements\Achievements;
use BradieTilley\Achievements\AchievementsConfig;
use BradieTilley\Achievements\Casts\CriteriaSerializationCast;
use BradieTilley\Achievements\Contracts\EarnsAchievements;
use BradieTilley\Achievements\Database\Factories\AchievementFactory;
use BradieTilley\Achievements\Objects\Criteria;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\QueryException;
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

    public function listenToEloquent(string|Model $model, string ...$events): static
    {
        $model = $model instanceof Model ? $model::class : $model;
        $events = Arr::map($events, fn (string $event) => "eloquent.{$event}: {$model}");

        return $this->listenTo(...$events);
    }

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

    public function give(Model&EarnsAchievements $user): static
    {
        try {
            $userAchievement = UserAchievement::getConfiguredClass();
            $userAchievement = new $userAchievement([
                'user_type' => $user->getMorphClass(),
                'user_id' => $user->getKey(),
                'achievement_id' => $this->getKey(),
            ]);

            $this->userAchievements()->save($userAchievement);
        } catch (QueryException $e) {
            if (str_contains($e->getMessage(), 'UNIQUE constraint failed')) {
                return $this;
            }

            throw $e;
        }

        return $this;
    }

    public function revoke(Model&EarnsAchievements $user, bool $force = false): static
    {
        if ($this->reverseable || $force) {
            $existing = $this->userAchievements()->whereMorphedTo('user', $user)->first();

            $existing?->delete();
        }

        return $this;
    }

    public static function allCached(): Collection
    {
        return Achievements::make()->getAchievements();
    }

    /**
     * @param string|TAchievement $achievement
     * @return TAchievement
     */
    public static function findByName(string|self $achievement): self
    {
        if ($achievement instanceof self) {
            return $achievement;
        }

        return static::where('name', $achievement)->firstOrFail();
    }
}
