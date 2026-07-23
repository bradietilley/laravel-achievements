<?php

use BradieTilley\Achievements\Criteria\HasRelationSumCriteria;
use BradieTilley\Achievements\Models\Achievement;
use Carbon\CarbonImmutable;
use Workbench\App\Models\Post;

test('the HasRelationSumCriteria can correctly determine eligibility', function (array $views, int|float $amount, bool $eligible) {
    $criteria = new HasRelationSumCriteria('posts', 'views', $amount);
    $achievement = new Achievement();
    $user = create_a_user();

    foreach ($views as $viewCount) {
        Post::create([
            'user_id' => $user->id,
            'views' => $viewCount,
        ]);
    }

    expect($criteria->isEligible($achievement, $user, 'SomeEventNotRelevant', null, CarbonImmutable::now()))->toBe($eligible);
})->with([
    [[10, 20, 30], 60, true],
    [[10, 20, 30], 61, false],
    [[], 0, true],
    [[], 1, false],
]);

test('the HasRelationSumCriteria applies where constraints', function () {
    $criteria = new HasRelationSumCriteria('posts', 'views', 50, [
        ['field' => 'content', 'operator' => 'LIKE', 'value' => '%featured%'],
    ]);
    $achievement = new Achievement();
    $user = create_a_user();

    Post::create([
        'user_id' => $user->id,
        'content' => 'a featured post',
        'views' => 40,
    ]);
    Post::create([
        'user_id' => $user->id,
        'content' => 'regular post',
        'views' => 100,
    ]);

    expect($criteria->isEligible($achievement, $user, 'SomeEventNotRelevant', null, CarbonImmutable::now()))->toBeFalse();

    Post::create([
        'user_id' => $user->id,
        'content' => 'another featured post',
        'views' => 15,
    ]);

    expect($criteria->isEligible($achievement, $user, 'SomeEventNotRelevant', null, CarbonImmutable::now()))->toBeTrue();
});
