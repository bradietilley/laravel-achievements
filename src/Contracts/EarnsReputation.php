<?php

namespace BradieTilley\Achievements\Contracts;

use BradieTilley\Achievements\Models\Reputation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

interface EarnsReputation
{
    /**
     * @return MorphOne<Reputation, Model>
     */
    public function reputation(): MorphOne;

    public function getReputation(): Reputation;

    public function giveReputation(int $points = 1, ?string $message = null, ?Model $user = null): Reputation;
}
