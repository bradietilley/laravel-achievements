<?php

namespace BradieTilley\Achievements\Criteria;

use BradieTilley\Achievements\Contracts\EarnsAchievements;
use BradieTilley\Achievements\Models\Achievement;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

class FieldInCriteria extends Criteria
{
    /**
     * @param  array<int, mixed>  $values
     */
    public function __construct(
        public readonly string $field,
        public readonly array $values,
        public readonly bool $in = true,
    ) {
    }

    /**
     * @param  ?array<mixed>  $payload
     */
    public function isEligible(Achievement $achievement, Model&EarnsAchievements $user, string $event, ?array $payload, CarbonImmutable $now): bool
    {
        $actual = data_get($user, $this->field);
        $contains = in_array($actual, $this->values, true);

        return $this->in ? $contains : ! $contains;
    }
}
