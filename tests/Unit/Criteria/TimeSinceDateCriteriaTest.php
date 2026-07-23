<?php

use BradieTilley\Achievements\Criteria\TimeSinceCriteria;
use BradieTilley\Achievements\Enums\ComparisonOperator;
use BradieTilley\Achievements\Models\Achievement;
use Carbon\CarbonImmutable;
use Carbon\Unit;

test('the TimeSinceCriteria can resolve the threshold date', function () {
    $this->travelTo('2024-01-06 22:02:00');

    $threshold = function (int $value, Unit $unit): string {
        $criteria = new TimeSinceCriteria('foo', ComparisonOperator::GreaterThan, $value, $unit);

        return $criteria->getThresholdDate(CarbonImmutable::now())->toDateTimeString();
    };

    expect($threshold(1, Unit::Day))->toBe('2024-01-05 22:02:00');
    expect($threshold(2, Unit::Day))->toBe('2024-01-04 22:02:00');
});

test('the TimeSinceCriteria can correctly determine eligibility', function (string $current, ComparisonOperator $operator, int $value, Unit $unit, bool $eligible) {
    $this->travelTo('2024-05-06 22:02:00');

    $criteria = new TimeSinceCriteria('created_at', $operator, $value, $unit);
    $achievement = new Achievement();
    $user = create_a_user();

    $user->update([
        'created_at' => $current,
    ]);

    $this->travelTo($current);

    expect($criteria->isEligible($achievement, $user, 'SomeEventNotRelevant', null, CarbonImmutable::now()))->toBe($eligible);
})->with([
    ['2024-05-05 22:01:00', ComparisonOperator::GreaterThan, 1, Unit::Day, false],
    ['2024-05-05 22:02:00', ComparisonOperator::GreaterThan, 1, Unit::Day, false],
    ['2024-05-05 22:02:00', ComparisonOperator::GreaterThanOrEqual, 1, Unit::Day, false],
]);

test('the TimeSinceCriteria can be reconstituted from an array with enum values', function () {
    $criteria = TimeSinceCriteria::fromArray([
        'field' => 'created_at',
        'operator' => '>',
        'value' => 3,
        'unit' => 'day',
        'overflow' => true,
    ]);

    expect($criteria)
        ->field->toBe('created_at')
        ->operator->toBe(ComparisonOperator::GreaterThan)
        ->value->toBe(3)
        ->unit->toBe(Unit::Day)
        ->overflow->toBeTrue();
});
