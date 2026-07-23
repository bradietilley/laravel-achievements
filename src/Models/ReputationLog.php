<?php

namespace BradieTilley\Achievements\Models;

use BradieTilley\Achievements\AchievementsConfig;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $points
 * @property string|null $message
 * @property-read Reputation $reputation
 * @property-read Model|null $user
 */
#[Table('reputation_logs')]
#[Fillable([
    'reputation_id',
    'user_type',
    'user_id',
    'points',
    'message',
])]
class ReputationLog extends Model
{
    public const UPDATED_AT = null;

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
