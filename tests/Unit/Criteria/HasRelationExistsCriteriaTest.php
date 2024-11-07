<?php

use BradieTilley\Achievements\Criteria\HasRelationExistsCriteria;
use BradieTilley\Achievements\Models\Achievement;
use Workbench\App\Models\Post;

test('the HasRelationExistsCriteria can correctly determine eligibility', function (int $count, int $min, bool $eligible) {
    $criteria = new HasRelationExistsCriteria('posts');
    $achievement = new Achievement();

    $user = create_a_user();
    expect($criteria->isEligible($achievement, $user, 'SomeEventNotRelevant', null))->toBe(false);

    $post = Post::create([
        'user_id' => $user->id,
    ]);
    expect($criteria->isEligible($achievement, $user, 'SomeEventNotRelevant', null))->toBe(true);

    $criteria = new HasRelationExistsCriteria('posts', false);
    expect($criteria->isEligible($achievement, $user, 'SomeEventNotRelevant', null))->toBe(false);

    $post->delete();
    expect($criteria->isEligible($achievement, $user, 'SomeEventNotRelevant', null))->toBe(true);
})->with([
    [ 3, 5, false, ],
    [ 4, 5, false, ],
    [ 5, 5, true, ],
    [ 6, 5, true, ],
]);
