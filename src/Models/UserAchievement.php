<?php

namespace BradieTilley\Achievements\Models;

use BradieTilley\Achievements\AchievementsConfig;
use BradieTilley\Achievements\Contracts\EarnsAchievements;
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
