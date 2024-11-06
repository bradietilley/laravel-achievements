<?php

use BradieTilley\Achievements\Criteria\HasAchievementsCriteria;
use BradieTilley\Achievements\Models\Achievement;

test('the HasAchievementsCriteria can correctly determine eligibility', function (int $require, bool $eligible) {
    $names = collect([
        'Has This',
        'Has That',
        'Did This',
        'Did That',
    ]);

    $require = $names->take($require)->all();

    $achievements = Achievement::factory()
        ->count(4)
        ->state(fn () => [
            'name' => $names->shift(),
        ])
        ->create()
        ->keyBy('name');

    $criteria = new HasAchievementsCriteria($require);
    $achievement = new Achievement();
    $user = create_a_user();

    $user->giveAchievement($achievements['Has This']);
    $user->giveAchievement($achievements['Has That']);
    $user->giveAchievement($achievements['Did This']);

    expect($criteria->isEligible($achievement, $user, 'SomeEventNotRelevant', null))->toBe($eligible);
})->with([
    [ 2, false, ],
    [ 3, false, ],
    [ 4, false, ],
]);
