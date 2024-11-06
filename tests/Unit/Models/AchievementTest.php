<?php

use BradieTilley\Achievements\Achievements;
use BradieTilley\Achievements\Models\Achievement;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Auth\Events\Login;
use Workbench\App\Models\User;

test('an achievement model can be created', function () {
    $achievement = Achievement::create([
        'name' => 'First Contribution',
        'reverseable' => false,
        'criteria' => [],
        'events' => [],
        'tier' => 'bronze',
    ]);

    expect($achievement)
        ->name->toBe('First Contribution')
        ->reverseable->toBe(false)
        ->criteria->toBe([])
        ->tier->toBe('bronze');
});

test('achievements can be cached', function () {
    expect(Achievement::allCached())->toHaveCount(0);

    $achievements = Achievement::factory(10)->createQuietly();
    expect(Achievement::allCached())->toHaveCount(0);

    Achievements::make()->regenerateCache();
    expect(Achievement::allCached())->toHaveCount(10);
});

test('an achievement model can have events', function () {
    $user = create_a_user();
    $this->actingAs($user);

    $achievement1 = Achievement::factory()
        ->events([ Authenticated::class ])
        ->createOne();

    $achievement1->listenToEloquent(User::class, 'updated')->save();
    expect($achievement1->events)->toBe([
        Authenticated::class,
        'eloquent.updated: Workbench\\App\\Models\\User',
    ]);

    $achievements = Achievements::make();
    $achievements->regenerateCache();
    $achievements->registerEventListener();

    $achievement2 = Achievement::factory()
        ->events([ Authenticated::class, Login::class ])
        ->createOne();

    expect($achievement2->events)->toBe([
        Authenticated::class,
        Login::class,
    ]);

    $achievements->regenerateCache();

    expect($achievements->getEvents())->toBe([
        Authenticated::class,
        'eloquent.updated: Workbench\App\Models\User',
        Login::class,
    ]);
});
