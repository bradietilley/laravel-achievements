<?php

use BradieTilley\Achievements\Models\Achievement;
use BradieTilley\Achievements\Models\UserAchievement;

test('a user can be granted achievements', function (bool $model) {
    $user = create_a_user();

    $achievement = Achievement::factory()->name('First Contribution')->createOne();
    expect(UserAchievement::count())->toBe(0);

    $user->giveAchievement($model ? $achievement : 'First Contribution');
    expect(UserAchievement::count())->toBe(1);

    expect(UserAchievement::first())
        ->achievement_id->toBe($achievement->id)
        ->user_type->toBe($user->getMorphClass())
        ->user_id->toBe($user->getKey());
})->with([
    true,
    false,
]);

test('can check if a user has an achievement', function () {
    $user = create_a_user();

    $achievement = Achievement::factory()->name('First Contribution')->createOne();
    expect($user->hasAchievement($achievement))->toBe(false);

    $user->giveAchievement($achievement);

    expect($user->hasAchievement($achievement))->toBe(true);
});

test('can safely re-grant the same achievement without erroring', function () {
    $user = create_a_user();

    $achievement = Achievement::factory()->name('First Contribution')->createOne();
    expect($user->hasAchievement($achievement))->toBe(false);

    $user->giveAchievement($achievement);
    $user->giveAchievement($achievement);
    $user->giveAchievement($achievement);
    expect($user->hasAchievement($achievement))->toBe(true);
});

test('can revoke achievements', function () {
    $user = create_a_user();

    $achievement = Achievement::factory()->name('First Contribution')->reverseable()->createOne();
    expect(UserAchievement::count())->toBe(0);
    expect($user->hasAchievement($achievement))->toBe(false);

    $user->giveAchievement($achievement);
    expect(UserAchievement::count())->toBe(1);
    expect($user->hasAchievement($achievement))->toBe(true);

    $user->revokeAchievement($achievement);
    expect(UserAchievement::count())->toBe(0);
    expect($user->hasAchievement($achievement))->toBe(false);
});
