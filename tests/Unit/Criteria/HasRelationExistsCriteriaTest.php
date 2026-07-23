<?php

use BradieTilley\Achievements\Criteria\HasRelationExistsCriteria;
use BradieTilley\Achievements\Models\Achievement;
use Carbon\CarbonImmutable;
use Workbench\App\Models\Post;

test('the HasRelationExistsCriteria can correctly determine eligibility', function () {
    $criteria = new HasRelationExistsCriteria('posts');
    $achievement = new Achievement();

    $user = create_a_user();
    expect($criteria->isEligible($achievement, $user, 'SomeEventNotRelevant', null, CarbonImmutable::now()))->toBe(false);

    $post = Post::create([
        'user_id' => $user->id,
    ]);
    expect($criteria->isEligible($achievement, $user, 'SomeEventNotRelevant', null, CarbonImmutable::now()))->toBe(true);

    $criteria = new HasRelationExistsCriteria('posts', false);
    expect($criteria->isEligible($achievement, $user, 'SomeEventNotRelevant', null, CarbonImmutable::now()))->toBe(false);

    $post->delete();
    expect($criteria->isEligible($achievement, $user, 'SomeEventNotRelevant', null, CarbonImmutable::now()))->toBe(true);
});

test('the HasRelationExistsCriteria applies where constraints', function () {
    $criteria = new HasRelationExistsCriteria('posts', true, [
        ['field' => 'content', 'operator' => 'LIKE', 'value' => '%featured%'],
    ]);
    $achievement = new Achievement();
    $user = create_a_user();

    Post::create([
        'user_id' => $user->id,
        'content' => 'regular post',
    ]);

    expect($criteria->isEligible($achievement, $user, 'SomeEventNotRelevant', null, CarbonImmutable::now()))->toBeFalse();

    Post::create([
        'user_id' => $user->id,
        'content' => 'a featured article',
    ]);

    expect($criteria->isEligible($achievement, $user, 'SomeEventNotRelevant', null, CarbonImmutable::now()))->toBeTrue();
});
