<?php

namespace BradieTilley\Achievements\Criteria;

use BradieTilley\Achievements\Contracts\EarnsAchievements;
use BradieTilley\Achievements\Models\Achievement;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

class TimeOfDayCriteria extends Criteria
{
    public function __construct(
        public readonly string $from,
        public readonly string $to,
    ) {
    }

    /**
     * @param  ?array<mixed>  $payload
     */
    public function isEligible(Achievement $achievement, Model&EarnsAchievements $user, string $event, ?array $payload, CarbonImmutable $now): bool
    {
        $from = $this->parseTimeOnDate($this->from, $now);
        $to = $this->parseTimeOnDate($this->to, $now);

        if ($from->lte($to)) {
            return $now->betweenIncluded($from, $to);
        }

        // Overnight window (e.g. 22:00–06:00)
        return $now->gte($from) || $now->lte($to);
    }

    protected function parseTimeOnDate(string $time, CarbonImmutable $date): CarbonImmutable
    {
        return CarbonImmutable::parse($date->toDateString().' '.$time);
    }
}
