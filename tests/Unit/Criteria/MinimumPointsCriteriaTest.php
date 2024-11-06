<?php

use BradieTilley\Achievements\Models\Achievement;
use BradieTilley\Achievements\Objects\MinimumPointsCriteria;

test('the MinimumPointsCriteria can correctly determine eligibility', function (int $points, int $min, bool $eligible) {
    $criteria = new MinimumPointsCriteria($min);
    $achievement = new Achievement();
    $user = create_a_user();
    $user->getReputation()->setPoints($points);

    expect($criteria->isEligible($achievement, $user, 'SomeEventNotRelevant', null))->toBe($eligible);
})->with([
    [ 9, 10, false, ],
    [ 10, 10, true, ],
    [ 11, 10, true, ],
    [ 9999, 10000, false, ],
    [ 10000, 10000, true, ],
]);
