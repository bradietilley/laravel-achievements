<?php

namespace BradieTilley\Achievements\Models;

use BradieTilley\Achievements\Achievements;
use BradieTilley\Achievements\AchievementsConfig;
use BradieTilley\Achievements\Contracts\EarnsReputation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $points
 * @property Model&EarnsReputation $model
 * @property-read Collection<int, ReputationLog> $logs
 */
class Reputation extends Model
{
    public $table = 'reputations';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
        ];
    }

    /**
     * @return class-string<self>
     */
    public static function alias(): string
    {
        return AchievementsConfig::getReputationModel();
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function user(): MorphTo
    {
        return $this->morphTo('user');
    }

    /**
     * @return HasMany<ReputationLog, $this>
     */
    public function logs(): HasMany
    {
        return $this->hasMany(ReputationLog::alias(), 'reputation_id');
    }

    public function addPoints(int $points = 1, ?string $message = null, ?Model $user = null): Reputation
    {
        $user ??= Achievements::make()->user();

        $log = ReputationLog::alias();
        $log = new $log([
            'reputation_id' => $this->getKey(),
            'user_type' => $user?->getMorphClass(),
            'user_id' => $user?->getKey(),
            'points' => $points,
            'message' => $message,
        ]);
        $log->save();

        $this->increment('points', $points);

        return $this;
    }

    public function setPoints(int $points, ?string $message = null, ?Model $user = null): Reputation
    {
        return $this->addPoints($points - $this->points, $message, $user);
    }
}
