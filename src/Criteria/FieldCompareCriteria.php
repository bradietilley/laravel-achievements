<?php

namespace BradieTilley\Achievements\Criteria;

use BradieTilley\Achievements\Contracts\EarnsAchievements;
use BradieTilley\Achievements\Enums\ComparisonOperator;
use BradieTilley\Achievements\Models\Achievement;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

class FieldCompareCriteria extends Criteria
{
    public function __construct(
        public readonly string $field,
        public readonly ComparisonOperator $operator,
        public readonly mixed $value,
    ) {
    }

    /**
     * @param  ?array<mixed>  $payload
     */
    public function isEligible(Achievement $achievement, Model&EarnsAchievements $user, string $event, ?array $payload, CarbonImmutable $now): bool
    {
        $actual = data_get($user, $this->field);

        if ($actual === null) {
            return false;
        }

        return match ($this->operator) {
            ComparisonOperator::Equal => $actual === $this->value,
            ComparisonOperator::NotEqual => $actual !== $this->value,
            ComparisonOperator::LessThan,
            ComparisonOperator::LessThanOrEqual,
            ComparisonOperator::GreaterThan,
            ComparisonOperator::GreaterThanOrEqual => $this->compareNumeric($actual),
        };
    }

    protected function compareNumeric(mixed $actual): bool
    {
        if (! is_numeric($actual) || ! is_numeric($this->value)) {
            return false;
        }

        $left = $actual + 0;
        $right = $this->value + 0;

        return match ($this->operator) {
            ComparisonOperator::LessThan => $left < $right,
            ComparisonOperator::LessThanOrEqual => $left <= $right,
            ComparisonOperator::GreaterThan => $left > $right,
            ComparisonOperator::GreaterThanOrEqual => $left >= $right,
            default => false,
        };
    }
}
