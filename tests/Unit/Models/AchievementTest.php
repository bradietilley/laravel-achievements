<?php

use BradieTilley\Achievements\Listeners\EventListener;
use BradieTilley\Achievements\Models\Achievement;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Cache;

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

    $achievements = Achievement::factory(10)->create();
    expect(Achievement::allCached())->toHaveCount(0);

    Cache::forget(Achievement::CACHE_KEY);
    expect(Achievement::allCached())->toHaveCount(10);
});

test('an achievement model can have events', function () {
    $achievement1 = Achievement::factory()
        ->events([ Authenticated::class ])
        ->createOne();
    expect($achievement1->events)->toBe([
        Authenticated::class,
    ]);

    $achievement2 = Achievement::factory()
        ->events([ Authenticated::class, Login::class ])
        ->createOne();
    expect($achievement2->events)->toBe([
        Authenticated::class,
        Login::class,
    ]);

    // Achievement::findByEvent(Authenticated::class);

    expect(Achievement::getEventMap())->toBe([
        Authenticated::class => EventListener::class,
        Login::class => EventListener::class,
    ]);
});
