<?php

use BradieTilley\Achievements\Criteria\CurrentDateCriteria;
use BradieTilley\Achievements\Models\Achievement;
use Carbon\Carbon;

test('the CurrentDateCriteria can correctly determine eligibility', function (string $current, string|DateTimeInterface $match, bool $eligible) {
    $criteria = new CurrentDateCriteria($match);
    $achievement = new Achievement();
    $user = create_a_user();

    $this->travelTo($current);

    expect($criteria->isEligible($achievement, $user, 'SomeEventNotRelevant', null))->toBe($eligible);
})->with([
    [ '2024-05-05 00:00:00', '2024-05-05', true, ],
    [ '2024-05-05 00:00:00', Carbon::parse('2024-05-05'), true, ],

    [ '2024-05-04 23:59:59', '2024-05-05', false, ],
    [ '2024-05-04 23:59:59', Carbon::parse('2024-05-05'), false, ],

    [ '2024-05-06 00:00:00', '2024-05-05', false, ],
    [ '2024-05-06 00:00:00', Carbon::parse('2024-05-05'), false, ],
]);
