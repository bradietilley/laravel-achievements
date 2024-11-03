<?php

namespace BradieTilley\Achievements;

use BradieTilley\Achievements\Listeners\EventListener;
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
     * Get the event listener class to use
     *
     * @return class-string<EventListener>
     */
    public static function getListenerClass(): string
    {
        return static::get('classes.event_listener', EventListener::class);
    }
}
