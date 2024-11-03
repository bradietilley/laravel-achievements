<?php

namespace BradieTilley\Achievements\Models;

use BradieTilley\Achievements\AchievementsConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property-read Model&EarnsAchievements $model
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

    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }

    public function achievement(): BelongsTo
    {
        return $this->belongsTo(AchievementsConfig::getAchievementModel(), 'achievement_id');
    }
}
