<?php

use BradieTilley\Achievements\Criteria\HasRelationCountCriteria;
use BradieTilley\Achievements\Models\Achievement;

test('the HasFieldCountCriteria can correctly determine eligibility', function (int $count, int $min, bool $eligible) {
    $criteria = new HasRelationCountCriteria('logins', $min);
    $achievement = new Achievement();
    $user = create_a_user();

    $user->fill([
        'logins' => $count,
    ]);
    expect($criteria->isEligible($achievement, $user, 'SomeEventNotRelevant', null))->toBe($eligible);
})->with([
    [ 3, 5, false, ],
    [ 4, 5, false, ],
    [ 5, 5, true, ],
    [ 6, 5, true, ],
]);
