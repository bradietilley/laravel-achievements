<?php

namespace BradieTilley\Achievements\Criteria;

use BradieTilley\Achievements\Contracts\EarnsAchievements;
use BradieTilley\Achievements\Models\Achievement;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

class HasRelationExistsCriteria extends RelationCriteria
{
    /**
     * @param  array<string, mixed>|list<array{field: string, value: mixed, operator?: string}>  $where
     */
    public function __construct(
        string $relation,
        public readonly bool $exists = true,
        array $where = [],
    ) {
        parent::__construct($relation, $where);
    }

    /**
     * @param  ?array<mixed>  $payload
     */
    public function isEligible(Achievement $achievement, Model&EarnsAchievements $user, string $event, ?array $payload, CarbonImmutable $now): bool
    {
        $relation = $this->relationQuery($user);

        if ($relation === null) {
            return false;
        }

        return $relation->exists() === $this->exists;
    }
}
