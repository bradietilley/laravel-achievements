<?php

namespace BradieTilley\Achievements\Criteria;

use BradieTilley\Achievements\Contracts\EarnsAchievements;
use BradieTilley\Achievements\Models\Achievement;
use Illuminate\Database\Eloquent\Model;

class HasFIeldValueCriteria extends Criteria
{
    public function __construct(public string $field, public mixed $value)
    {
    }

    public function isEligible(Achievement $achievement, Model&EarnsAchievements $user, string $event, array|null $payload): bool
    {
        $value = data_get($user, $this->field);

        return $value >= $this->value;
    }
}
