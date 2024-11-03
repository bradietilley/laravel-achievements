<?php

use BradieTilley\Achievements\Models\Achievement;

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
