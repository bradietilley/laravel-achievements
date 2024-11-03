<?php

namespace BradieTilley\Achievements\Models;

use BradieTilley\Achievements\AchievementsConfig;
use BradieTilley\Achievements\Contracts\EarnsAchievements;
use BradieTilley\Achievements\Database\Factories\AchievementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

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

    public const CACHE_KEY = 'bradietilley_achievements';

    public const CACHE_KEY_MAP = 'bradietilley_achievement.map';

    public $table = 'achievements';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'criteria' => 'array',
            'events' => 'array',
            'reverseable' => 'boolean',
            'async' => 'boolean',
        ];
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
        return $this->hasMany(AchievementsConfig::getUserAchievementModel());
    }

    public function give(Model&EarnsAchievements $user): static
    {
        try {
            $model = AchievementsConfig::getUserAchievementModel();
            $model = new $model([
                'model_type' => $user->getMorphClass(),
                'model_id' => $user->getKey(),
                'achievement_id' => $this->getKey(),
            ]);

            $this->userAchievements()->save($model);
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
            $existing = $this->userAchievements()->whereMorphedTo('model', $user)->first();

            $existing?->delete();
        }

        return $this;
    }

    public static function allCached(): Collection
    {
        return Cache::remember(
            static::CACHE_KEY,
            now()->addHour(),
            fn () => static::all()->collect(),
        );
    }

    public static function getEventMap(): array
    {
        return Cache::remember(
            static::CACHE_KEY_MAP,
            now()->addHour(),
            fn () => static::getEventMapRaw(),
        );
    }

    protected static function getEventMapRaw(): array
    {
        $class = AchievementsConfig::getListenerClass();

        return []; // todo
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
