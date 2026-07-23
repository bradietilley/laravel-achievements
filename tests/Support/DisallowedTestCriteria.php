<?php

namespace Tests\Support;

use BradieTilley\Achievements\Contracts\EarnsAchievements;
use BradieTilley\Achievements\Criteria\Criteria;
use BradieTilley\Achievements\Models\Achievement;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

class DisallowedTestCriteria extends Criteria
{
    public function isEligible(Achievement $achievement, Model&EarnsAchievements $user, string $event, ?array $payload, CarbonImmutable $now): bool
    {
        return true;
    }
}
