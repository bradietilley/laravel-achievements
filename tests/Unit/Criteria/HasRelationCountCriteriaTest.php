<?php

use BradieTilley\Achievements\Criteria\HasRelationCountCriteria;
use BradieTilley\Achievements\Models\Achievement;
use Illuminate\Support\Collection;
use Workbench\App\Models\Post;

test('the HasRelationCount can correctly determine eligibility', function (int $count, int $min, bool $eligible) {
    $criteria = new HasRelationCountCriteria('posts', $min);
    $achievement = new Achievement();
    $user = create_a_user();

    Collection::range(1, $count)->each(
        fn () => Post::create([
            'user_id' => $user->id,
        ]),
    );
    expect($criteria->isEligible($achievement, $user, 'SomeEventNotRelevant', null))->toBe($eligible);
})->with([
    [ 3, 5, false, ],
    [ 4, 5, false, ],
    [ 5, 5, true, ],
    [ 6, 5, true, ],
]);
