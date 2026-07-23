<?php

use BradieTilley\Achievements\Criteria\FieldCompareCriteria;
use BradieTilley\Achievements\Enums\ComparisonOperator;
use BradieTilley\Achievements\Models\Achievement;
use Carbon\CarbonImmutable;

test('the FieldCompareCriteria can correctly determine eligibility', function (mixed $actual, ComparisonOperator $operator, mixed $value, bool $eligible) {
    $criteria = new FieldCompareCriteria('logins', $operator, $value);
    $achievement = new Achievement();
    $user = create_a_user();

    $user->fill([
        'logins' => $actual,
    ]);

    expect($criteria->isEligible($achievement, $user, 'SomeEventNotRelevant', null, CarbonImmutable::now()))->toBe($eligible);
})->with([
    [5, ComparisonOperator::Equal, 5, true],
    [5, ComparisonOperator::Equal, 4, false],
    [5, ComparisonOperator::NotEqual, 4, true],
    [5, ComparisonOperator::NotEqual, 5, false],
    [5, ComparisonOperator::GreaterThan, 4, true],
    [5, ComparisonOperator::GreaterThan, 5, false],
    [5, ComparisonOperator::GreaterThanOrEqual, 5, true],
    [5, ComparisonOperator::LessThan, 6, true],
    [5, ComparisonOperator::LessThanOrEqual, 5, true],
    [null, ComparisonOperator::Equal, null, false],
    ['abc', ComparisonOperator::GreaterThan, 1, false],
]);
