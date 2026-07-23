<?php

use BradieTilley\Achievements\Criteria\TimeOfDayCriteria;
use BradieTilley\Achievements\Models\Achievement;
use Carbon\CarbonImmutable;

test('the TimeOfDayCriteria can correctly determine eligibility for same-day windows', function (string $current, string $from, string $to, bool $eligible) {
    $criteria = new TimeOfDayCriteria($from, $to);
    $achievement = new Achievement();
    $user = create_a_user();

    $this->travelTo($current);

    expect($criteria->isEligible($achievement, $user, 'SomeEventNotRelevant', null, CarbonImmutable::now()))->toBe($eligible);
})->with([
    ['2024-05-05 09:00:00', '09:00', '17:00', true],
    ['2024-05-05 12:30:00', '09:00:00', '17:00:00', true],
    ['2024-05-05 08:59:59', '09:00', '17:00', false],
    ['2024-05-05 17:00:00', '09:00', '17:00', true],
    ['2024-05-05 17:00:01', '09:00', '17:00', false],
]);

test('the TimeOfDayCriteria can correctly determine eligibility for overnight windows', function (string $current, string $from, string $to, bool $eligible) {
    $criteria = new TimeOfDayCriteria($from, $to);
    $achievement = new Achievement();
    $user = create_a_user();

    $this->travelTo($current);

    expect($criteria->isEligible($achievement, $user, 'SomeEventNotRelevant', null, CarbonImmutable::now()))->toBe($eligible);
})->with([
    ['2024-05-05 22:00:00', '22:00', '06:00', true],
    ['2024-05-05 23:30:00', '22:00', '06:00', true],
    ['2024-05-05 05:00:00', '22:00', '06:00', true],
    ['2024-05-05 06:00:00', '22:00', '06:00', true],
    ['2024-05-05 12:00:00', '22:00', '06:00', false],
    ['2024-05-05 21:59:59', '22:00', '06:00', false],
]);
