<?php

namespace BradieTilley\Achievements\Criteria;

use BradieTilley\Achievements\Contracts\EarnsAchievements;
use BradieTilley\Achievements\Models\Achievement;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

class CurrentDateCriteria extends Criteria
{
    public function __construct(public string|DateTimeInterface $date)
    {
    }

    public function isEligible(Achievement $achievement, Model&EarnsAchievements $user, string $event, array|null $payload): bool
    {
        $sameDay = Carbon::now()->isSameDay($this->date);

        return $sameDay;
    }
}
