<?php

namespace BradieTilley\Achievements\Objects;

use BradieTilley\Achievements\Contracts\EarnsAchievements;
use BradieTilley\Achievements\Contracts\EarnsReputation;
use BradieTilley\Achievements\Models\Achievement;
use Illuminate\Database\Eloquent\Model;

class MinimumPointsCriteria extends Criteria
{
    public function __construct(public int $points)
    {
    }

    public function isEligible(Achievement $achievement, Model&EarnsAchievements $user, string $event, array|null $payload): bool
    {
        if (! $user instanceof EarnsReputation) {
            return false;
        }

        $points = $user->getReputation()->points;

        return $points >= $this->points;
    }
}