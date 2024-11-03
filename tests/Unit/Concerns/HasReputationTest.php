<?php

use BradieTilley\Achievements\Models\Reputation;

test('a user will be given a new reputation if one does not exist', function () {
    $user = create_a_user();
    expect(Reputation::count())->toBe(0);

    $reputation = $user->reputation;
    expect($reputation)->toBeInstanceOf(Reputation::class);
    expect(Reputation::count())->toBe(1);
    $user = $user->withoutRelations()->refresh();

    $reputation = $user->reputation;
    expect($reputation)->toBeInstanceOf(Reputation::class);
    expect(Reputation::count())->toBe(1);
});

test('reputation points can be added to a user', function () {
    $user = create_a_user();

    $user->addReputation(10);
    expect($user->reputation->points)->toBe(10);

    $user->addReputation(5);
    expect($user->reputation->points)->toBe(15);
});
