<?php

namespace BradieTilley\Achievements\Concerns;

use BradieTilley\Achievements\AchievementsConfig;
use BradieTilley\Achievements\Models\Achievement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;

/**
 * @mixin Model
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Achievement> $achievements
 */
trait HasAchievements
{
    public function achievements(): MorphToMany
    {
        return $this->morphToMany(AchievementsConfig::getAchievementModel(), 'model', 'user_achievement', 'model_id', 'achievement_id');
    }

    /**
     * @return Collection<int, Achievement>
     */
    public function getAchievements(): Collection
    {
        return $this->achievements->collect();
    }

    public function hasAchievement(string|Achievement $achievement): bool
    {
        return $this->achievements()->where('achievement_id', Achievement::findByName($achievement)->id)->exists();
    }

    public function giveAchievement(string|Achievement $achievement): static
    {
        Achievement::findByName($achievement)->give($this);

        return $this;
    }

    public function revokeAchievement(string|Achievement $achievement): static
    {
        Achievement::findByName($achievement)->revoke($this);

        return $this;
    }
}
