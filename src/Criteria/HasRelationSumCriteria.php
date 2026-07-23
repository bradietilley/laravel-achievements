<?php

namespace BradieTilley\Achievements\Criteria;

use BradieTilley\Achievements\Contracts\EarnsAchievements;
use BradieTilley\Achievements\Models\Achievement;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

class HasRelationSumCriteria extends RelationCriteria
{
    /**
     * @param  array<string, mixed>|list<array{field: string, value: mixed, operator?: string}>  $where
     */
    public function __construct(
        string $relation,
        public readonly string $column,
        public readonly int|float $amount,
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

        $sum = $relation->sum($this->column);

        if (! is_numeric($sum)) {
            return false;
        }

        return (float) $sum >= $this->amount;
    }
}
