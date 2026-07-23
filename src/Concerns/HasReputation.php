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
    /**
     * @return MorphOne<Reputation, $this>
     */
    public function reputation(): MorphOne
    {
        $class = Reputation::alias();

        return $this->morphOne($class, 'user', 'user_type', 'user_id')
            ->withDefault(function () use ($class) {
                if ($this->exists === false) {
                    return null;
                }

                return $class::query()->firstOrCreate(
                    [
                        'user_type' => $this->getMorphClass(),
                        'user_id' => $this->getKey(),
                    ],
                    [
                        'points' => 0,
                    ],
                );
            });
    }

    public function getReputation(): Reputation
    {
        return $this->reputation;
    }

    public function giveReputation(int $points = 1, ?string $message = null, ?Model $user = null): Reputation
    {
        return $this->reputation->addPoints($points, $message, $user);
    }
}
