<?php

namespace BradieTilley\Achievements\Models;

use BradieTilley\Achievements\AchievementsConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $points
 * @property string $message
 * @property ReputationLog $reputation
 * @property-read Collection<int, ReputationLog> $history
 * @property-read User $user
 */
class ReputationLog extends Model
{
    public const UPDATED_AT = null;

    public $table = 'reputation_logs';

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
        return AchievementsConfig::getReputationLogModel();
    }

    /**
     * @return BelongsTo<Reputation, $this>
     */
    public function reputation(): BelongsTo
    {
        return $this->belongsTo(Reputation::alias(), 'reputation_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function user(): MorphTo
    {
        return $this->morphTo('user');
    }
}
