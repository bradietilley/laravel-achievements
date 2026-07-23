<?php

namespace BradieTilley\Achievements\Criteria;

use BradieTilley\Achievements\Contracts\EarnsAchievements;
use BradieTilley\Achievements\Enums\ComparisonOperator;
use BradieTilley\Achievements\Models\Achievement;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\Unit;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class TimeSinceCriteria extends Criteria
{
    public function __construct(
        public readonly string $field,
        public readonly ComparisonOperator $operator,
        public readonly int $value,
        public readonly Unit $unit,
        public readonly bool $overflow = false,
    ) {
    }

    /**
     * @param  ?array<mixed>  $payload
     */
    public function isEligible(Achievement $achievement, Model&EarnsAchievements $user, string $event, ?array $payload, CarbonImmutable $now): bool
    {
        $value = data_get($user, $this->field);

        if ($value === null) {
            return false;
        }

        try {
            /** @phpstan-ignore-next-line */
            $value = Carbon::parse($value);
        } catch (Throwable) {
            return false;
        }

        $threshold = $this->getThresholdDate($now);

        return match ($this->operator) {
            ComparisonOperator::LessThan => $threshold->lt($value),
            ComparisonOperator::LessThanOrEqual => $threshold->lte($value),
            ComparisonOperator::Equal => $threshold->isSameDay($value),
            ComparisonOperator::NotEqual => ! $threshold->isSameDay($value),
            ComparisonOperator::GreaterThan => $threshold->gt($value),
            ComparisonOperator::GreaterThanOrEqual => $threshold->gte($value),
        };
    }

    public function getThresholdDate(CarbonImmutable $now): CarbonImmutable
    {
        return $now->subUnit($this->unit, $this->value, $this->overflow);
    }
}
