<?php

namespace BradieTilley\Achievements\Models;

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
    public static function getConfiguredClass(): string
    {
        return AchievementsConfig::getReputationModel();
    }

    public function user(): MorphTo
    {
        return $this->morphTo('user');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ReputationLog::getConfiguredClass(), 'reputation_id');
    }

    public function addPoints(int $points = 1, ?string $message = null, ?Model $user = null): Reputation
    {
        $log = ReputationLog::getConfiguredClass();
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
}
