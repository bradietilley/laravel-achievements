<?php

namespace BradieTilley\Achievements\Criteria;

use BradieTilley\Achievements\Contracts\EarnsAchievements;
use BradieTilley\Achievements\Models\Achievement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

/**
 * @property array<int, string> $achievements
 */
class HasAchievementsCriteria extends Criteria
{
    /**
     * @param array<int, string> $achievements
     */
    public function __construct(public array $achievements)
    {
    }

    /**
     * @param null|array<mixed> $payload
     */
    public function isEligible(Achievement $achievement, Model&EarnsAchievements $user, string $event, array|null $payload): bool
    {
        $achievementIds = Arr::map($this->achievements, function (string $achievement) {
            return Achievement::findByName($achievement);
        });

        /**
         * Achievements have unique names so if we ask for 10
         * and get 10 back then all are present and accounted for
         */
        $count = $user->achievements()
            ->whereIn(
                'achievement_id',
                $achievementIds,
            )
            ->count();

        return $count == count($this->achievements);
    }
}
