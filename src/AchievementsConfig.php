<?php

namespace BradieTilley\Achievements;

use BradieTilley\Achievements\Events\AchievementGranted;
use BradieTilley\Achievements\Events\AchievementRevoked;
use BradieTilley\Achievements\Jobs\ProcessAchievement;
use BradieTilley\Achievements\Models\Achievement;
use BradieTilley\Achievements\Models\Reputation;
use BradieTilley\Achievements\Models\ReputationLog;
use BradieTilley\Achievements\Models\UserAchievement;

class AchievementsConfig
{
    /**
     * Get the Achievement model class
     *
     * @return class-string<Achievement>
     */
    public static function getAchievementModel(): string
    {
        /** @phpstan-ignore-next-line */
        return config('achievements.models.achievement', Achievement::class);
    }

    /**
     * Get the UserAchievement (pivot) model class
     *
     * @return class-string<UserAchievement>
     */
    public static function getUserAchievementModel(): string
    {
        /** @phpstan-ignore-next-line */
        return config('achievements.models.user_achievement', UserAchievement::class);
    }

    /**
     * Get the Reputation model class
     *
     * @return class-string<Reputation>
     */
    public static function getReputationModel(): string
    {
        /** @phpstan-ignore-next-line */
        return config('achievements.models.reputation', Reputation::class);
    }

    /**
     * Get the ReputationLog model class
     *
     * @return class-string<ReputationLog>
     */
    public static function getReputationLogModel(): string
    {
        /** @phpstan-ignore-next-line */
        return config('achievements.models.reputation_log', ReputationLog::class);
    }

    /**
     * Get the ProcessAchievement job to use
     *
     * @return class-string<ProcessAchievement>
     */
    public static function getProcessAchievementJob(): string
    {
        /** @phpstan-ignore-next-line */
        return config('achievements.jobs.process_achievement', ProcessAchievement::class);
    }

    /**
     * Get the AchievementGranted event to use
     *
     * @return class-string<AchievementGranted>
     */
    public static function getAchievementGrantedEvent(): string
    {
        /** @phpstan-ignore-next-line */
        return config('achievements.events.achievement_granted', AchievementGranted::class);
    }

    /**
     * Get the AchievementRevoked event to use
     *
     * @return class-string<AchievementRevoked>
     */
    public static function getAchievementRevokedEvent(): string
    {
        /** @phpstan-ignore-next-line */
        return config('achievements.events.achievement_revoked', AchievementRevoked::class);
    }
}
