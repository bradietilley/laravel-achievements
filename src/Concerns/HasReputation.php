<?php

namespace BradieTilley\Achievements\Concerns;

use BradieTilley\Achievements\Models\Reputation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @mixin Model
 *
 * @property-read Reputation $reputation
 */
trait HasReputation
{
    public function reputation(): MorphOne
    {
        return $this->morphOne(Reputation::alias(), 'user', 'user_type', 'user_id')
            ->withDefault(function () {
                if ($this->exists === false) {
                    return null;
                }

                return Reputation::create([
                    'points' => 0,
                    'user_type' => $this->getMorphClass(),
                    'user_id' => $this->getKey(),
                ]);
            });
    }

    public function getReputation(): Reputation
    {
        return $this->reputation;
    }

    public function giveReputation(int $points = 1, ?string $message = null, Model|null $user = null): Reputation
    {
        return $this->reputation->addPoints($points, $message, $user);
    }
}
