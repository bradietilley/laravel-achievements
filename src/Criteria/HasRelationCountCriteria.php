<?php

namespace BradieTilley\Achievements\Criteria;

use BradieTilley\Achievements\Contracts\EarnsAchievements;
use BradieTilley\Achievements\Models\Achievement;
use Illuminate\Database\Eloquent\Model;

class HasRelationCountCriteria extends Criteria
{
    public function __construct(public string $relation, public int $count)
    {
    }

    /**
     * @param null|array<mixed> $payload
     */
    public function isEligible(Achievement $achievement, Model&EarnsAchievements $user, string $event, array|null $payload): bool
    {
        $count = $user->{$this->relation}()->count();

        return $count >= $this->count;
    }
}
