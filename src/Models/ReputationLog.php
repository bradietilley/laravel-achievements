<?php

namespace BradieTilley\Achievements\Models;

use BradieTilley\Achievements\AchievementsConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $points
 * @property ReputationLog $reputation
 * @property-read Collection<int, ReputationLog> $history
 * @property-read User $user
 */
class ReputationLog extends Model
{
    public $table = 'reputations_logs';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
        ];
    }

    public function reputation(): BelongsTo
    {
        return $this->belongsTo(AchievementsConfig::getReputationModel(), 'reputation_id');
    }

    public function user(): MorphTo
    {
        return $this->morphTo('user');
    }
}
