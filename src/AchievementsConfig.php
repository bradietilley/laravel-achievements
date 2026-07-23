<?php

namespace BradieTilley\Achievements;

use BradieTilley\Achievements\Criteria\Criteria;
use BradieTilley\Achievements\Criteria\CurrentDateCriteria;
use BradieTilley\Achievements\Criteria\DateBetweenCriteria;
use BradieTilley\Achievements\Criteria\DayOfWeekCriteria;
use BradieTilley\Achievements\Criteria\FieldCompareCriteria;
use BradieTilley\Achievements\Criteria\FieldHasValueCriteria;
use BradieTilley\Achievements\Criteria\FieldInCriteria;
use BradieTilley\Achievements\Criteria\FieldIsNullCriteria;
use BradieTilley\Achievements\Criteria\HasAchievementsCriteria;
use BradieTilley\Achievements\Criteria\HasFieldCountCriteria;
use BradieTilley\Achievements\Criteria\HasRelationCountCriteria;
use BradieTilley\Achievements\Criteria\HasRelationExistsCriteria;
use BradieTilley\Achievements\Criteria\HasRelationSumCriteria;
use BradieTilley\Achievements\Criteria\MinimumPointsCriteria;
use BradieTilley\Achievements\Criteria\MonthCriteria;
use BradieTilley\Achievements\Criteria\TimeOfDayCriteria;
use BradieTilley\Achievements\Criteria\TimeSinceCriteria;
use BradieTilley\Achievements\Events\AchievementGranted;
use BradieTilley\Achievements\Events\AchievementRevoked;
use BradieTilley\Achievements\Jobs\ProcessAchievement;
use BradieTilley\Achievements\Models\Achievement;
use BradieTilley\Achievements\Models\Reputation;
use BradieTilley\Achievements\Models\ReputationLog;
use BradieTilley\Achievements\Models\UserAchievement;
use InvalidArgumentException;
use UnitEnum;

class AchievementsConfig
{
    /**
     * Built-in criteria classes that are always allowed.
     *
     * @var list<class-string<Criteria>>
     */
    public const BUILTIN_CRITERIA = [
        CurrentDateCriteria::class,
        DateBetweenCriteria::class,
        DayOfWeekCriteria::class,
        FieldCompareCriteria::class,
        FieldHasValueCriteria::class,
        FieldInCriteria::class,
        FieldIsNullCriteria::class,
        HasAchievementsCriteria::class,
        HasFieldCountCriteria::class,
        HasRelationCountCriteria::class,
        HasRelationExistsCriteria::class,
        HasRelationSumCriteria::class,
        MinimumPointsCriteria::class,
        MonthCriteria::class,
        TimeOfDayCriteria::class,
        TimeSinceCriteria::class,
    ];

    /**
     * Get the Achievement model class
     *
     * @return class-string<Achievement>
     */
    public static function getAchievementModel(): string
    {
        return static::resolveClassString(
            config('achievements.models.achievement'),
            Achievement::class,
            Achievement::class,
        );
    }

    /**
     * Get the UserAchievement (pivot) model class
     *
     * @return class-string<UserAchievement>
     */
    public static function getUserAchievementModel(): string
    {
        return static::resolveClassString(
            config('achievements.models.user_achievement'),
            UserAchievement::class,
            UserAchievement::class,
        );
    }

    /**
     * Get the Reputation model class
     *
     * @return class-string<Reputation>
     */
    public static function getReputationModel(): string
    {
        return static::resolveClassString(
            config('achievements.models.reputation'),
            Reputation::class,
            Reputation::class,
        );
    }

    /**
     * Get the ReputationLog model class
     *
     * @return class-string<ReputationLog>
     */
    public static function getReputationLogModel(): string
    {
        return static::resolveClassString(
            config('achievements.models.reputation_log'),
            ReputationLog::class,
            ReputationLog::class,
        );
    }

    /**
     * Get the ProcessAchievement job to use
     *
     * @return class-string<ProcessAchievement>
     */
    public static function getProcessAchievementJob(): string
    {
        return static::resolveClassString(
            config('achievements.jobs.process_achievement'),
            ProcessAchievement::class,
            ProcessAchievement::class,
        );
    }

    /**
     * Get the AchievementGranted event to use
     *
     * @return class-string<AchievementGranted>
     */
    public static function getAchievementGrantedEvent(): string
    {
        return static::resolveClassString(
            config('achievements.events.achievement_granted'),
            AchievementGranted::class,
            AchievementGranted::class,
        );
    }

    /**
     * Get the AchievementRevoked event to use
     *
     * @return class-string<AchievementRevoked>
     */
    public static function getAchievementRevokedEvent(): string
    {
        return static::resolveClassString(
            config('achievements.events.achievement_revoked'),
            AchievementRevoked::class,
            AchievementRevoked::class,
        );
    }

    /**
     * Get all allowed criteria class names.
     *
     * @return list<class-string<Criteria>>
     */
    public static function getAllowedCriteriaClasses(): array
    {
        $configured = config('achievements.criteria', []);

        if (! is_array($configured)) {
            $configured = [];
        }

        $allowed = self::BUILTIN_CRITERIA;

        foreach ($configured as $class) {
            if (! is_string($class) || ! is_a($class, Criteria::class, true)) {
                throw new InvalidArgumentException('Configured achievement criteria must be Criteria subclasses.');
            }

            $allowed[] = $class;
        }

        return array_values(array_unique($allowed));
    }

    /**
     * Determine whether the given criteria class is allowlisted.
     *
     * @param  class-string  $class
     */
    public static function isCriteriaClassAllowed(string $class): bool
    {
        return in_array($class, static::getAllowedCriteriaClasses(), true);
    }

    /**
     * @template T of object
     *
     * @param  class-string<T>  $default
     * @param  class-string<T>  $base
     * @return class-string<T>
     */
    protected static function resolveClassString(mixed $configured, string $default, string $base): string
    {
        $class = is_string($configured) && $configured !== '' ? $configured : $default;

        if (! is_a($class, $base, true)) {
            throw new InvalidArgumentException("Configured class [{$class}] must be a subclass of [{$base}].");
        }

        return $class;
    }

    protected static function resolveNullableString(string $key): string|null
    {
        $value = config($key);

        if ($value === null || is_string($value)) {
            return $value;
        }

        throw new InvalidArgumentException("Configured value for [{$key}] must be a string or null.");
    }

    protected static function resolveNullableStringOrEnum(string $key): string|UnitEnum|null
    {
        $value = self::resolveNullableString($key);

        if ($value === null || is_string($value) || is_a($value, UnitEnum::class, true)) {
            return $value;
        }

        throw new InvalidArgumentException("Configured value for [{$key}] must be a string or null.");
    }

    /**
     * Get the queue connection to use for the ProcessAchievement job
     */
    public static function getJobConnection(): string|UnitEnum|null
    {
        return self::resolveNullableStringOrEnum('achievements.jobs.connection');
    }

    /**
     * Get the queue to use for the ProcessAchievement job
     */
    public static function getJobQueue(): string|UnitEnum|null
    {
        return self::resolveNullableStringOrEnum('achievements.jobs.queue');
    }
}
