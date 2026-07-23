<?php

use BradieTilley\Achievements\Criteria\HasAchievementsCriteria;
use BradieTilley\Achievements\Models\Achievement;
use Carbon\CarbonImmutable;

test('the HasAchievementsCriteria can correctly determine eligibility', function (int $require, bool $eligible) {
    $names = collect([
        'Has This',
        'Has That',
        'Did This',
        'Did That',
    ]);

    $required = $names->take($require)->all();

    $achievements = Achievement::factory()
        ->count(4)
        ->state(fn () => [
            'name' => $names->shift(),
        ])
        ->create()
        ->keyBy('name');

    $criteria = new HasAchievementsCriteria($required);
    $achievement = new Achievement();
    $user = create_a_user();

    $user->giveAchievement($achievements['Has This']);
    $user->giveAchievement($achievements['Has That']);
    $user->giveAchievement($achievements['Did This']);

    expect($criteria->isEligible($achievement, $user, 'SomeEventNotRelevant', null, CarbonImmutable::now()))->toBe($eligible);
})->with([
    [2, true],
    [3, true],
    [4, false],
]);

test('the HasAchievementsCriteria fails when a required achievement does not exist', function () {
    $criteria = new HasAchievementsCriteria(['Missing Achievement']);
    $achievement = new Achievement();
    $user = create_a_user();

    expect($criteria->isEligible($achievement, $user, 'SomeEventNotRelevant', null, CarbonImmutable::now()))->toBe(false);
});
