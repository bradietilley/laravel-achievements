<?php

namespace Workbench\App\Models;

use BradieTilley\Achievements\Concerns\HasAchievements;
use BradieTilley\Achievements\Concerns\HasReputation;
use BradieTilley\Achievements\Contracts\EarnsAchievements;
use BradieTilley\Achievements\Contracts\EarnsReputation;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Support\Facades\Event;
use Workbench\App\Events\BasicExampleEvent;

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

    public function doSomething(): void
    {
        Event::dispatch(new BasicExampleEvent());
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'user_id');
    }
}
