<?php

use BradieTilley\Achievements\Criteria\FieldHasValueCriteria;
use BradieTilley\Achievements\Models\Achievement;

test('the  FieldHasValueCriteria can correctly determine eligibility', function (int $count, int $value, bool $eligible) {
    $criteria = new FieldHasValueCriteria('logins', $value);
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
    [ 6, 5, false, ],
]);
