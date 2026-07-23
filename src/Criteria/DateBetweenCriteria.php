<?php

namespace BradieTilley\Achievements\Criteria;

use BradieTilley\Achievements\Contracts\EarnsAchievements;
use BradieTilley\Achievements\Models\Achievement;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

class DateBetweenCriteria extends Criteria
{
    public function __construct(
        public readonly string|DateTimeInterface $from,
        public readonly string|DateTimeInterface $to,
    ) {
    }

    /**
     * @param  ?array<mixed>  $payload
     */
    public function isEligible(Achievement $achievement, Model&EarnsAchievements $user, string $event, ?array $payload, CarbonImmutable $now): bool
    {
        return $now->betweenIncluded($this->from, $this->to);
    }
}
