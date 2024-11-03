<?php

use Workbench\App\Models\User;

uses(Tests\TestCase::class)->in('Feature', 'Unit');

if (! function_exists('create_a_user')) {
    function create_a_user(): User
    {
        return User::create([
            'name' => "Test",
            'email' => hrtime(true).'@example.org',
            'password' => '',
        ]);
    }
}
