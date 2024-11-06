<?php

namespace BradieTilley\Achievements\Events;

use BradieTilley\Achievements\AchievementsConfig;
use BradieTilley\Achievements\Contracts\EarnsAchievements;
use BradieTilley\Achievements\Models\Achievement;
use Illuminate\Database\Eloquent\Model;

class AchievementRevoked
{
    public function __construct(public readonly Achievement $achievement, public readonly Model&EarnsAchievements $user)
    {
    }

    public static function alias(): string
    {
        return AchievementsConfig::getAchievementRevokedEvent();
    }
}
