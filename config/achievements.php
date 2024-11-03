<?php

use BradieTilley\Achievements\Listeners\EventListener;
use BradieTilley\Achievements\Models\Achievement;
use BradieTilley\Achievements\Models\Reputation;
use BradieTilley\Achievements\Models\ReputationLog;
use BradieTilley\Achievements\Models\UserAchievement;

return [
    'models' => [
        'achievement' => Achievement::class,

        'user_achievement' => UserAchievement::class,

        'reputation' => Reputation::class,

        'reputation_log' => ReputationLog::class,
    ],

    'classes' => [
        'event_listener' => EventListener::class,
    ],
];
