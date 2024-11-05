<?php

use BradieTilley\Achievements\Achievements;
use BradieTilley\Achievements\Models\Achievement;
use Illuminate\Support\Facades\Auth;
use Workbench\App\Events\BasicExampleEvent;

test('an achievement can be automatically granted', function () {
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
