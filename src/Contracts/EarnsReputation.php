<?php

namespace BradieTilley\Achievements\Contracts;

use BradieTilley\Achievements\Models\Reputation;
use Illuminate\Database\Eloquent\Model;

interface EarnsReputation
{
    public function getReputation(): Reputation;

    public function addReputation(int $points = 1, ?string $message = null, (Model&EarnsReputation)|null $user = null): Reputation;
}
