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
    /** @var array<string, mixed> */
    protected static array $cache = [];

    protected static function get(string $key, mixed $default = null): mixed
    {
        return static::$cache[$key] ??= config("achievements.{$key}", $default);
    }

    public static function clearCache(): void
    {
        static::$cache = [];
    }

    /**
     * Get the Achievement model class
     *
     * @return class-string<Achievement>
     */
    public static function getAchievementModel(): string
    {
        return static::get('models.achievement', Achievement::class);
    }

    /**
     * Get the UserAchievement (pivot) model class
     *
     * @return class-string<UserAchievement>
     */
    public static function getUserAchievementModel(): string
    {
        return static::get('models.user_achievement', UserAchievement::class);
    }

    /**
     * Get the Reputation model class
     *
     * @return class-string<Reputation>
     */
    public static function getReputationModel(): string
    {
        return static::get('models.reputation', Reputation::class);
    }

    /**
     * Get the ReputationLog model class
     *
     * @return class-string<ReputationLog>
     */
    public static function getReputationLogModel(): string
    {
        return static::get('models.reputation_log', ReputationLog::class);
    }

    /**
     * Get the ProcessAchievement job to use
     *
     * @return class-string<ProcessAchievement>
     */
    public static function getProcessAchievementJob(): string
    {
        return static::get('jobs.process_achievement', ProcessAchievement::class);
    }

    /**
     * Get the AchievementGranted event to use
     *
     * @return class-string<AchievementGranted>
     */
    public static function getAchievementGrantedEvent(): string
    {
        return static::get('events.achievement_granted', AchievementGranted::class);
    }

    /**
     * Get the AchievementRevoked event to use
     *
     * @return class-string<AchievementRevoked>
     */
    public static function getAchievementRevokedEvent(): string
    {
        return static::get('events.achievement_revoked', AchievementRevoked::class);
    }
}
