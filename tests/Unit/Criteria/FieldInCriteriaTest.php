<?php

use BradieTilley\Achievements\Criteria\FieldInCriteria;
use BradieTilley\Achievements\Models\Achievement;
use Carbon\CarbonImmutable;

test('the FieldInCriteria can correctly determine eligibility', function (mixed $actual, array $values, bool $in, bool $eligible) {
    $criteria = new FieldInCriteria('logins', $values, $in);
    $achievement = new Achievement();
    $user = create_a_user();

    $user->fill([
        'logins' => $actual,
    ]);

    expect($criteria->isEligible($achievement, $user, 'SomeEventNotRelevant', null, CarbonImmutable::now()))->toBe($eligible);
})->with([
    [5, [1, 5, 10], true, true],
    [5, [1, 10], true, false],
    [5, [1, 10], false, true],
    [5, [1, 5, 10], false, false],
    [null, [null], true, true],
]);
