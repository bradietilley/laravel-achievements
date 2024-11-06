<?php

namespace BradieTilley\Achievements\Criteria;

use BradieTilley\Achievements\Contracts\EarnsAchievements;
use BradieTilley\Achievements\Models\Achievement;
use Carbon\Carbon;
use Carbon\Unit;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class TimeSinceCriteria extends Criteria
{
    public function __construct(public string $field, public string $operator, public int $value, public Unit|string $unit, public bool $overflow = false)
    {
    }

    public function isEligible(Achievement $achievement, Model&EarnsAchievements $user, string $event, array|null $payload): bool
    {
        $value = data_get($user, $this->field);

        if ($value === null) {
            return false;
        }

        try {
            $value = Carbon::parse($value);
        } catch (Throwable) {
            return false;
        }

        $threshold = $this->getThresholdDate();

        $eligible = match ($this->operator) {
            '<' => $threshold->lt($value),
            '<=' => $threshold->lte($value),
            '=' => $threshold->isSameDay($value),
            '!=' => ! $threshold->isSameDay($value),
            '>' => $threshold->gt($value),
            '>=' => $threshold->gte($value),
            default => false,
        };

        return $eligible;
    }

    public function getThresholdDate(): Carbon
    {
        return Carbon::now()->sub($this->unit, $this->value, $this->overflow);
    }
}
