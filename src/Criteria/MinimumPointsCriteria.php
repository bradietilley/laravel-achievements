<?php

namespace BradieTilley\Achievements\Criteria;

use BradieTilley\Achievements\Contracts\EarnsAchievements;
use BradieTilley\Achievements\Contracts\EarnsReputation;
use BradieTilley\Achievements\Models\Achievement;
use Illuminate\Database\Eloquent\Model;

class MinimumPointsCriteria extends Criteria
{
    public function __construct(public int $points)
    {
    }

    /**
     * @param null|array<mixed> $payload
     */
    public function isEligible(Achievement $achievement, Model&EarnsAchievements $user, string $event, array|null $payload): bool
    {
        if (! $user instanceof EarnsReputation) {
            return false;
        }

        $points = $user->getReputation()->points;

        return $points >= $this->points;
    }
}
