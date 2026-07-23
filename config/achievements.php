<?php

use BradieTilley\Achievements\Events\AchievementGranted;
use BradieTilley\Achievements\Events\AchievementRevoked;
use BradieTilley\Achievements\Jobs\ProcessAchievement;
use BradieTilley\Achievements\Models\Achievement;
use BradieTilley\Achievements\Models\Reputation;
use BradieTilley\Achievements\Models\ReputationLog;
use BradieTilley\Achievements\Models\UserAchievement;

return [
    'models' => [
        /**
         * Get the Achievement model class
         *
         * @var class-string<Achievement>
         */
        'achievement' => Achievement::class,

        /**
         * Get the UserAchievement (pivot) model class
         *
         * @var class-string<UserAchievement>
         */
        'user_achievement' => UserAchievement::class,

        /**
         * Get the Reputation model class
         *
         * @var class-string<Reputation>
         */
        'reputation' => Reputation::class,

        /**
         * Get the ReputationLog model class
         *
         * @var class-string<ReputationLog>
         */
        'reputation_log' => ReputationLog::class,
    ],

    'jobs' => [
        /**
         * Get the ProcessAchievement job to use
         *
         * @var class-string<ProcessAchievement>
         */
        'process_achievement' => ProcessAchievement::class,

        /**
         * Optional default queue for ProcessAchievement via Queue::route().
         */
        'queue' => null,

        /**
         * Optional default connection for ProcessAchievement via Queue::route().
         */
        'connection' => null,
    ],

    'events' => [
        /**
         * Get the AchievementGranted event to use
         *
         * @var class-string<AchievementGranted>
         */
        'achievement_granted' => AchievementGranted::class,

        /**
         * Get the AchievementRevoked event to use
         *
         * @var class-string<AchievementRevoked>
         */
        'achievement_revoked' => AchievementRevoked::class,
    ],

    /**
     * Additional Criteria subclasses allowed when deserializing achievement criteria.
     * Built-in package criteria are always allowed.
     *
     * @var list<class-string<\BradieTilley\Achievements\Criteria\Criteria>>
     */
    'criteria' => [
        // App\Achievements\Criteria\CustomCriteria::class,
    ],
];
