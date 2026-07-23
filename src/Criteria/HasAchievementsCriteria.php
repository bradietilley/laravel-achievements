<?php

namespace BradieTilley\Achievements\Criteria;

use BradieTilley\Achievements\Contracts\EarnsAchievements;
use BradieTilley\Achievements\Models\Achievement;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property array<int, string> $achievements
 */
class HasAchievementsCriteria extends Criteria
{
    /**
     * @param  array<int, string>  $achievements
     */
    public function __construct(public readonly array $achievements)
    {
    }

    /**
     * @param  ?array<mixed>  $payload
     */
    public function isEligible(Achievement $achievement, Model&EarnsAchievements $user, string $event, ?array $payload, CarbonImmutable $now): bool
    {
        $required = array_values(array_unique($this->achievements));

        if ($required === []) {
            return true;
        }

        $count = $user->achievements()
            ->whereIn('name', $required)
            ->count();

        /**
         * Achievements have unique names so if we ask for N
         * and get N back then all are present and accounted for.
         */
        return $count === count($required);
    }
}
