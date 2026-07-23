<?php

namespace BradieTilley\Achievements\Criteria;

use BradieTilley\Achievements\Contracts\EarnsAchievements;
use BradieTilley\Achievements\Models\Achievement;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

class MonthCriteria extends Criteria
{
    /**
     * @param  array<int, int>  $months  Calendar months (1–12)
     */
    public function __construct(
        public readonly array $months,
    ) {
    }

    /**
     * @param  ?array<mixed>  $payload
     */
    public function isEligible(Achievement $achievement, Model&EarnsAchievements $user, string $event, ?array $payload, CarbonImmutable $now): bool
    {
        if ($this->months === []) {
            return false;
        }

        return in_array($now->month, $this->months, true);
    }
}
