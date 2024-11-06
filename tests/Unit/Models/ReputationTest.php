<?php

use BradieTilley\Achievements\Models\ReputationLog;
use Workbench\App\Models\User;

test('a ReputationLog is written when points are added', function () {
    $user = create_a_user();

    $logs = function () use ($user): array {
        $logs = $user->reputation->logs()->get()->map(
            fn (ReputationLog $log) => array_filter([
                'points' => $log->points,
                'message' => $log->message,
                'user_type' => $log->user_type,
                'user_id' => $log->user_id,
            ]),
        )->all();

        $user->reputation->logs()->delete();

        return $logs;
    };

    expect($logs())->toBe([]);

    $user->giveReputation(10);
    expect($logs())->toBe([
        [ 'points' => 10, ],
    ]);

    $user->giveReputation(-10);
    expect($logs())->toBe([
        [ 'points' => -10, ],
    ]);

    $user->giveReputation(1);
    $user->giveReputation(2);
    $user->giveReputation(3);
    expect($logs())->toBe([
        [ 'points' => 1, ],
        [ 'points' => 2, ],
        [ 'points' => 3, ],
    ]);

    $user->giveReputation(5000, 'Good lad, does amazing work');
    $user->giveReputation(-1000, 'Naughty boy');
    expect($logs())->toBe([
        [
            'points' => 5000,
            'message' => 'Good lad, does amazing work',
        ],
        [
            'points' => -1000,
            'message' => 'Naughty boy',
        ],
    ]);

    $admin = create_a_user();
    $other = create_a_user();
    $this->actingAs($admin);

    $user->giveReputation(50);
    $user->giveReputation(55, user: $other);
    expect($logs())->toBe([
        [
            'points' => 50,
            'user_type' => User::class,
            'user_id' => $admin->id,
        ],
        [
            'points' => 55,
            'user_type' => User::class,
            'user_id' => $other->id,
        ],
    ]);
});
