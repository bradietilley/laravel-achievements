<?php

namespace BradieTilley\Achievements\Contracts;

use BradieTilley\Achievements\Models\Achievement;
use Illuminate\Support\Collection;

interface EarnsAchievements
{
    public function getAchievements(): Collection;

    public function giveAchievement(string|Achievement $achievement): static;

    public function revokeAchievement(string|Achievement $achievement): static;
}
