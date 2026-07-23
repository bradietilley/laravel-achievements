<?php

use BradieTilley\Achievements\Criteria\MonthCriteria;
use BradieTilley\Achievements\Models\Achievement;
use Carbon\CarbonImmutable;

test('the MonthCriteria can correctly determine eligibility', function (string $current, array $months, bool $eligible) {
    $criteria = new MonthCriteria($months);
    $achievement = new Achievement();
    $user = create_a_user();

    $this->travelTo($current);

    expect($criteria->isEligible($achievement, $user, 'SomeEventNotRelevant', null, CarbonImmutable::now()))->toBe($eligible);
})->with([
    ['2024-05-05 12:00:00', [5], true],
    ['2024-05-05 12:00:00', [1, 12], false],
    ['2024-12-25 12:00:00', [12], true],
    ['2024-05-05 12:00:00', [], false],
]);
