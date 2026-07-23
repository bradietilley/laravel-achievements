<?php

namespace BradieTilley\Achievements\Criteria;

use BradieTilley\Achievements\Contracts\EarnsAchievements;
use BradieTilley\Achievements\Models\Achievement;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

class DayOfWeekCriteria extends Criteria
{
    /**
     * @param  array<int, int>  $days  Carbon day-of-week integers (0 = Sunday … 6 = Saturday)
     */
    public function __construct(
        public readonly array $days,
    ) {
    }

    /**
     * @param  ?array<mixed>  $payload
     */
    public function isEligible(Achievement $achievement, Model&EarnsAchievements $user, string $event, ?array $payload, CarbonImmutable $now): bool
    {
        if ($this->days === []) {
            return false;
        }

        return in_array($now->dayOfWeek, $this->days, true);
    }
}
