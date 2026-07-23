<?php

use BradieTilley\Achievements\Criteria\DayOfWeekCriteria;
use BradieTilley\Achievements\Models\Achievement;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

test('the DayOfWeekCriteria can correctly determine eligibility', function (string $current, array $days, bool $eligible) {
    $criteria = new DayOfWeekCriteria($days);
    $achievement = new Achievement();
    $user = create_a_user();

    $this->travelTo($current);

    expect($criteria->isEligible($achievement, $user, 'SomeEventNotRelevant', null, CarbonImmutable::now()))->toBe($eligible);
})->with([
    // 2024-05-05 is a Sunday (0)
    ['2024-05-05 12:00:00', [Carbon::SUNDAY], true],
    ['2024-05-05 12:00:00', [Carbon::MONDAY, Carbon::FRIDAY], false],
    ['2024-05-06 12:00:00', [Carbon::MONDAY], true],
    ['2024-05-05 12:00:00', [], false],
]);
