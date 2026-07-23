<?php

namespace BradieTilley\Achievements\Models;

use BradieTilley\Achievements\AchievementsConfig;
use BradieTilley\Achievements\Contracts\EarnsAchievements;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property-read Model&EarnsAchievements $user
 * @property-read Achievement $achievement
 */
#[Table('user_achievement')]
#[Fillable([
    'user_type',
    'user_id',
    'achievement_id',
])]
class UserAchievement extends Model
{
    /**
     * @return class-string<self>
     */
    public static function alias(): string
    {
        return AchievementsConfig::getUserAchievementModel();
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function user(): MorphTo
    {
        return $this->morphTo('user');
    }

    /**
     * @return BelongsTo<Achievement, $this>
     */
    public function achievement(): BelongsTo
    {
        return $this->belongsTo(Achievement::alias(), 'achievement_id');
    }
}
