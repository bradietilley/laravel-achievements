<?php

use BradieTilley\Achievements\Achievements;
use BradieTilley\Achievements\Jobs\ProcessAchievement;
use BradieTilley\Achievements\Models\Achievement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Workbench\App\Events\BasicExampleEvent;

test('achievement cache is not rebuilt until first read', function () {
    Achievement::factory()->createQuietly();

    DB::flushQueryLog();
    DB::enableQueryLog();

    Achievements::make()->registerEventListener();

    expect(collect(DB::getQueryLog())->contains(
        fn (array $query): bool => str_contains($query['query'], 'achievements'),
    ))->toBeFalse();

    Achievements::make()->getAchievements();

    expect(collect(DB::getQueryLog())->contains(
        fn (array $query): bool => str_contains($query['query'], 'achievements'),
    ))->toBeTrue();
});

test('async achievement jobs receive the event payload', function () {
    Queue::fake();

    $achievement = Achievement::factory()
        ->async()
        ->events([BasicExampleEvent::class])
        ->criteria([])
        ->createOne();

    Achievements::make()->regenerateCache();

    $user = create_a_user();
    Auth::login($user);

    $user->doSomething();

    Queue::assertPushed(ProcessAchievement::class, function (ProcessAchievement $job) use ($achievement): bool {
        return $job->achievement->is($achievement)
            && $job->event === BasicExampleEvent::class
            && is_array($job->payload)
            && $job->payload !== [];
    });
});

test('findByName resolves achievements through the achievements cache', function () {
    $achievement = Achievement::factory()->name('Cached Lookup')->createOne();

    Cache::flush();
    Achievements::make()->regenerateCache();

    expect(Achievement::findByName('Cached Lookup')->is($achievement))->toBeTrue();
});

test('duplicate achievement grants are ignored via unique constraint', function () {
    $achievement = Achievement::factory()->createOne();
    $user = create_a_user();

    Achievements::make()->giveAchievement($achievement, $user);
    Achievements::make()->giveAchievement($achievement, $user);

    expect($user->achievements()->count())->toBe(1);
});
