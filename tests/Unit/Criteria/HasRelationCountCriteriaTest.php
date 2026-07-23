<?php

use BradieTilley\Achievements\Criteria\HasRelationCountCriteria;
use BradieTilley\Achievements\Models\Achievement;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Workbench\App\Models\Post;

test('the HasRelationCountCriteria can correctly determine eligibility', function (int $count, int $min, bool $eligible) {
    $criteria = new HasRelationCountCriteria('posts', $min);
    $achievement = new Achievement();
    $user = create_a_user();

    Collection::range(1, $count)->each(
        fn () => Post::create([
            'user_id' => $user->id,
        ]),
    );
    expect($criteria->isEligible($achievement, $user, 'SomeEventNotRelevant', null, CarbonImmutable::now()))->toBe($eligible);
})->with([
    [ 3, 5, false, ],
    [ 4, 5, false, ],
    [ 5, 5, true, ],
    [ 6, 5, true, ],
]);

test('the HasRelationCountCriteria applies associative where constraints', function () {
    $criteria = new HasRelationCountCriteria('posts', 2, [
        'content' => 'published',
    ]);
    $achievement = new Achievement();
    $user = create_a_user();

    Post::create([
        'user_id' => $user->id,
        'content' => 'published',
    ]);
    Post::create([
        'user_id' => $user->id,
        'content' => 'draft',
    ]);

    expect($criteria->isEligible($achievement, $user, 'SomeEventNotRelevant', null, CarbonImmutable::now()))->toBeFalse();

    Post::create([
        'user_id' => $user->id,
        'content' => 'published',
    ]);

    expect($criteria->isEligible($achievement, $user, 'SomeEventNotRelevant', null, CarbonImmutable::now()))->toBeTrue();
});

test('the HasRelationCountCriteria applies operator where constraints', function () {
    $criteria = new HasRelationCountCriteria('posts', 1, [
        ['field' => 'content', 'operator' => 'LIKE', 'value' => '%happy birthday%'],
    ]);
    $achievement = new Achievement();
    $user = create_a_user();

    Post::create([
        'user_id' => $user->id,
        'content' => 'hello world',
    ]);

    expect($criteria->isEligible($achievement, $user, 'SomeEventNotRelevant', null, CarbonImmutable::now()))->toBeFalse();

    Post::create([
        'user_id' => $user->id,
        'content' => 'wishing you a happy birthday today',
    ]);

    expect($criteria->isEligible($achievement, $user, 'SomeEventNotRelevant', null, CarbonImmutable::now()))->toBeTrue();
});
