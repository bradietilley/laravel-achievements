<?php

use BradieTilley\Achievements\Achievements;
use BradieTilley\Achievements\Models\Achievement;
use BradieTilley\Achievements\Objects\CurrentDateCriteria;
use BradieTilley\Achievements\Objects\MinimumPointsCriteria;
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
        ->state([
            'reverseable' => false,
        ])
        ->events([
            BasicExampleEvent::class,
        ])
        ->criteria([
            new MinimumPointsCriteria(100),
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

test('an achievement can be automatically granted if multiple criteria pass', function () {
    $achievement = Achievement::factory()
        ->state([
            'reverseable' => true,
        ])
        ->events([
            BasicExampleEvent::class,
        ])
        ->criteria([
            new MinimumPointsCriteria(100),
            new CurrentDateCriteria('2024-06-06'),
        ])
        ->createOne();

    Achievements::make()->regenerateCache();

    $user = create_a_user()->refresh();
    $user->giveReputation(101);

    Auth::login($user);
    expect($user->hasAchievement($achievement))->toBe(false);

    $user->doSomething();
    expect($user->hasAchievement($achievement))->toBe(false);

    $this->travelTo('2024-06-06');
    $user->doSomething();
    expect($user->hasAchievement($achievement))->toBe(true);

    $this->travelTo('2024-06-07');
    $user->doSomething();
    expect($user->hasAchievement($achievement))->toBe(false);

    $this->travelTo('2024-06-06');
    $user->giveReputation(-2);
    $user->doSomething();
    expect($user->hasAchievement($achievement))->toBe(false);

    $user->giveReputation(2);
    $user->doSomething();
    expect($user->hasAchievement($achievement))->toBe(true);
});
