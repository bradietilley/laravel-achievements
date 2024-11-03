<?php

namespace Workbench\App\Models;

use BradieTilley\Achievements\Concerns\HasAchievements;
use BradieTilley\Achievements\Contracts\EarnsAchievements;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as AuthUser;
use Workbench\App\Enums\UserStatusTestEnum;

class User extends AuthUser implements EarnsAchievements
{
    use HasAchievements;
    use SoftDeletes;

    public $table = 'users';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'integer_field' => 'integer',
            'decimal_field' => 'decimal:2',
            'string_field' => 'string',
            'date_field' => 'date',
            'datetime_field' => 'datetime',
            'enum_field' => UserStatusTestEnum::class,
            'array_field' => 'array',
        ];
    }
}
