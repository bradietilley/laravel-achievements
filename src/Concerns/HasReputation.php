<?php

namespace BradieTilley\Achievements\Concerns;

use BradieTilley\Achievements\Models\Reputation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @mixin Model
 *
 * @property-read Reputation $reputation
 */
trait HasReputation
{
    public function reputation(): MorphTo
    {
        return $this->morphTo()->withDefault(function () {
            return new Reputation([
                'points' => 0,
            ]);
        }); // todo
    }

    public function addReputation(int $points = 1, ?string $message = null, ?Model $user = null): Reputation
    {
        return $this->reputation->addPoints($points, $message, $user);
    }
}
