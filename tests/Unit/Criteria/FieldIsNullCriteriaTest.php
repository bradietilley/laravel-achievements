<?php

use BradieTilley\Achievements\Criteria\FieldIsNullCriteria;
use BradieTilley\Achievements\Models\Achievement;
use Carbon\CarbonImmutable;

test('the FieldIsNullCriteria can correctly determine eligibility', function (mixed $actual, bool $null, bool $eligible) {
    $criteria = new FieldIsNullCriteria('logins', $null);
    $achievement = new Achievement();
    $user = create_a_user();

    $user->fill([
        'logins' => $actual,
    ]);

    expect($criteria->isEligible($achievement, $user, 'SomeEventNotRelevant', null, CarbonImmutable::now()))->toBe($eligible);
})->with([
    [null, true, true],
    [null, false, false],
    [5, true, false],
    [5, false, true],
]);
