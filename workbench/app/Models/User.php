<?php

namespace Workbench\App\Models;

use BradieTilley\Achievements\Concerns\HasAchievements;
use BradieTilley\Achievements\Concerns\HasReputation;
use BradieTilley\Achievements\Contracts\EarnsAchievements;
use BradieTilley\Achievements\Contracts\EarnsReputation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as AuthUser;

class User extends AuthUser implements EarnsReputation, EarnsAchievements
{
    use HasReputation;
    use HasAchievements;
    use SoftDeletes;

    public $table = 'users';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
