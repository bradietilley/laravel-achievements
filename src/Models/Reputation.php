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

    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AchievementsConfig::getReputationLogModel(), 'reputation_id');
    }

    public function addPoints(int $points = 1, ?string $message = null, ?Model $user = null): static
    {
        $log = AchievementsConfig::getReputationLogModel();
        $log = new $log([
            'model_type' => $user?->getMorphClass(),
            'model_id' => $user?->getKey(),
            'points' => $points,
            'message' => $message,
        ]);
        $log->save();

        $this->increment('points', $points);

        return $this;
    }
}
