<?php

namespace BradieTilley\Achievements\Criteria;

use BradieTilley\Achievements\Contracts\EarnsAchievements;
use BradieTilley\Achievements\Models\Achievement;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

class HasFieldCountCriteria extends Criteria
{
    public function __construct(
        public readonly string $field,
        public readonly int $count,
    ) {
    }

    /**
     * @param  ?array<mixed>  $payload
     */
    public function isEligible(Achievement $achievement, Model&EarnsAchievements $user, string $event, ?array $payload, CarbonImmutable $now): bool
    {
        $count = data_get($user, $this->field);

        if (! is_numeric($count)) {
            return false;
        }

        return $count >= $this->count;
    }
}
