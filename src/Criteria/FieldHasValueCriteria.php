<?php

namespace BradieTilley\Achievements\Criteria;

use BradieTilley\Achievements\Contracts\EarnsAchievements;
use BradieTilley\Achievements\Models\Achievement;
use Illuminate\Database\Eloquent\Model;

class FieldHasValueCriteria extends Criteria
{
    public function __construct(public string $field, public mixed $value)
    {
    }

    /**
     * @param null|array<mixed> $payload
     */
    public function isEligible(Achievement $achievement, Model&EarnsAchievements $user, string $event, array|null $payload): bool
    {
        $value = data_get($user, $this->field);

        return $value === $this->value;
    }
}
