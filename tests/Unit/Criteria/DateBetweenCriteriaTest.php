<?php

use BradieTilley\Achievements\Criteria\DateBetweenCriteria;
use BradieTilley\Achievements\Models\Achievement;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

test('the DateBetweenCriteria can correctly determine eligibility', function (string $current, string $from, string $to, bool $eligible) {
    $criteria = new DateBetweenCriteria($from, $to);
    $achievement = new Achievement();
    $user = create_a_user();

    $this->travelTo($current);

    expect($criteria->isEligible($achievement, $user, 'SomeEventNotRelevant', null, CarbonImmutable::now()))->toBe($eligible);
})->with([
    ['2024-05-05 12:00:00', '2024-05-01 00:00:00', '2024-05-10 23:59:59', true],
    ['2024-05-01 00:00:00', '2024-05-01 00:00:00', '2024-05-10 23:59:59', true],
    ['2024-05-10 23:59:59', '2024-05-01 00:00:00', '2024-05-10 23:59:59', true],
    ['2024-04-30 23:59:59', '2024-05-01 00:00:00', '2024-05-10 23:59:59', false],
    ['2024-05-11 00:00:00', '2024-05-01 00:00:00', '2024-05-10 23:59:59', false],
    ['2024-05-05 12:00:00', '2024-05-01', '2024-05-10', true],
]);

test('the DateBetweenCriteria accepts DateTimeInterface bounds', function () {
    $criteria = new DateBetweenCriteria(
        Carbon::parse('2024-05-01'),
        Carbon::parse('2024-05-10 23:59:59'),
    );
    $achievement = new Achievement();
    $user = create_a_user();

    $this->travelTo('2024-05-05 12:00:00');

    expect($criteria->isEligible($achievement, $user, 'SomeEventNotRelevant', null, CarbonImmutable::now()))->toBeTrue();
});
