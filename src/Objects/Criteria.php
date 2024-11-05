<?php

namespace BradieTilley\Achievements\Objects;

use BradieTilley\Achievements\Contracts\EarnsAchievements;
use BradieTilley\Achievements\Models\Achievement;
use Illuminate\Database\Eloquent\Model;

abstract class Criteria
{
    abstract public function isEligible(Achievement $achievement, Model&EarnsAchievements $user, string $event, array|null $payload): bool;
}
