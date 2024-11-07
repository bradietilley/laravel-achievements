<?php

namespace BradieTilley\Achievements\Contracts;

use BradieTilley\Achievements\Models\Achievement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;

interface EarnsAchievements
{
    /**
     * @return MorphToMany<Achievement, Model>
     */
    public function achievements(): MorphToMany;

    /**
     * @return Collection<int, Achievement>
     */
    public function getAchievements(): Collection;

    public function giveAchievement(string|Achievement $achievement): static;

    public function revokeAchievement(string|Achievement $achievement): static;

    public function hasAchievement(string|Achievement $achievement): bool;
}
