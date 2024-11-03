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

    $user->addReputation(10);
    expect($logs())->toBe([
        [ 'points' => 10, ],
    ]);

    $user->addReputation(-10);
    expect($logs())->toBe([
        [ 'points' => -10, ],
    ]);

    $user->addReputation(1);
    $user->addReputation(2);
    $user->addReputation(3);
    expect($logs())->toBe([
        [ 'points' => 1, ],
        [ 'points' => 2, ],
        [ 'points' => 3, ],
    ]);

    $user->addReputation(5000, 'Good lad, does amazing work');
    $user->addReputation(-1000, 'Naughty boy');
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

    $user->addReputation(50);
    $user->addReputation(55, user: $other);
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
