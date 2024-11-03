<?php

namespace BradieTilley\Achievements\Contracts;

use BradieTilley\Achievements\Models\Reputation;

interface EarnsReputation
{
    public function getReputation(): Reputation;

    public function addReputation(int $points = 1, ?string $message = null): static;

    public function subReputation(int $points = 1, ?string $message = null): static;
}
