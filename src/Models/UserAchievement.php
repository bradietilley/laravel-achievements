<?php

namespace BradieTilley\Achievements\Models;

use BradieTilley\Achievements\AchievementsConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property-read Model&EarnsAchievements $user
 * @property-read Achievement $achievement
 */
class UserAchievement extends Model
{
    public $table = 'user_achievement';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
        ];
    }

    /**
     * @return class-string<self>
     */
    public static function alias(): string
    {
        return AchievementsConfig::getUserAchievementModel();
    }

    public function user(): MorphTo
    {
        return $this->morphTo('user');
    }

    public function achievement(): BelongsTo
    {
        return $this->belongsTo(Achievement::alias(), 'achievement_id');
    }
}
