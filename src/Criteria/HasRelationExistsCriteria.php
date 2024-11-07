<?php

namespace BradieTilley\Achievements\Criteria;

use BradieTilley\Achievements\Contracts\EarnsAchievements;
use BradieTilley\Achievements\Models\Achievement;
use Illuminate\Database\Eloquent\Model;

class HasRelationExistsCriteria extends Criteria
{
    public function __construct(public string $relation, public bool $exists = true)
    {
    }

    public function isEligible(Achievement $achievement, Model&EarnsAchievements $user, string $event, array|null $payload): bool
    {
        $exists = $user->{$this->relation}()->exists();

        return $exists == $this->exists;
    }
}
