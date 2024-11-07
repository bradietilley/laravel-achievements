<?php

use BradieTilley\Achievements\Criteria\TimeSinceCriteria;
use BradieTilley\Achievements\Models\Achievement;
use Carbon\Unit;

test('the TimeSinceCriteria can resolve the threshold date', function () {
    $this->travelTo('2024-01-06 22:02:00');

    $threshold = function (...$arguments): string {
        $criteria = new TimeSinceCriteria('foo', '>', ...$arguments);

        return $criteria->getThresholdDate()->toDateTimeString();
    };

    expect($threshold(1, 'day'))->toBe('2024-01-05 22:02:00');
    expect($threshold(2, 'day'))->toBe('2024-01-04 22:02:00');
});

test('the TimeSinceCriteria can correctly determine eligibility', function (string $current, string $operator, int $value, string|Unit $unit, bool $eligible) {
    $this->travelTo('2024-05-06 22:02:00');

    $criteria = new TimeSinceCriteria('created_at', $operator, $value, $unit);
    $achievement = new Achievement();
    $user = create_a_user();

    $user->update([
        'created_at' => $current,
    ]);

    $this->travelTo($current);

    expect($criteria->isEligible($achievement, $user, 'SomeEventNotRelevant', null))->toBe($eligible);
})->with([
    [ '2024-05-05 22:01:00', '>', 1, 'days', false, ],
    [ '2024-05-05 22:02:00', '>', 1, 'days', false, ],
    [ '2024-05-05 22:02:00', '>=', 1, 'days', false, ],
]);
