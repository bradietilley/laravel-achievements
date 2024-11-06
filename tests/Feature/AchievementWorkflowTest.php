<?php

use BradieTilley\Achievements\Achievements;
use BradieTilley\Achievements\Models\Achievement;
use BradieTilley\Achievements\Objects\CriteriaForReputationPoints;
use Illuminate\Support\Facades\Auth;
use Workbench\App\Events\BasicExampleEvent;

test('an achievement can be automatically granted if no criteria is added', function () {
    $achievement = Achievement::factory()->events([
        BasicExampleEvent::class,
    ])->criteria([])->createOne();

    Achievements::make()->regenerateCache();

    $user = create_a_user()->refresh();

    Auth::login($user);
    expect($user->hasAchievement($achievement))->toBe(false);

    $user->doSomething();
    expect($user->hasAchievement($achievement))->toBe(true);
});

test('an achievement can be automatically granted if a criteria is added and it passes', function () {
    $achievement = Achievement::factory()
        ->events([
            BasicExampleEvent::class,
        ])
        ->criteria([
            new CriteriaForReputationPoints(100),
        ])
        ->createOne();

    Achievements::make()->regenerateCache();

    $user = create_a_user()->refresh();

    Auth::login($user);
    expect($user->hasAchievement($achievement))->toBe(false);

    $user->giveReputation(99);
    $user->doSomething();
    expect($user->hasAchievement($achievement))->toBe(false);

    $user->giveReputation(1);
    $user->doSomething();
    expect($user->hasAchievement($achievement))->toBe(true);

    $user->giveReputation(-1);
    $user->doSomething();
    expect($user->hasAchievement($achievement))->toBe(true); // not reverseable

    $achievement->update([
        'reverseable' => true,
    ]);
    $user->doSomething();
    expect($user->hasAchievement($achievement))->toBe(false); // reversed now

    $user->giveReputation(1);
    $user->doSomething();
    expect($user->hasAchievement($achievement))->toBe(true);
});
