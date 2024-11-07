<?php

namespace BradieTilley\Achievements\Criteria;

use BradieTilley\Achievements\Contracts\EarnsAchievements;
use BradieTilley\Achievements\Models\Achievement;
use Illuminate\Database\Eloquent\Model;

class HasFieldCountCriteria extends Criteria
{
    public function __construct(public string $field, public int $count)
    {
    }

    /**
     * @param null|array<mixed> $payload
     */
    public function isEligible(Achievement $achievement, Model&EarnsAchievements $user, string $event, array|null $payload): bool
    {
        $count = data_get($user, $this->field);

        if (! is_numeric($count)) {
            return false;
        }

        return $count >= $this->count;
    }
}
