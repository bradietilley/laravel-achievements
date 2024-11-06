<?php

namespace BradieTilley\Achievements\Models\Observers;

use BradieTilley\Achievements\Achievements;
use BradieTilley\Achievements\Models\Achievement;

class AchievementObserver
{
    public function created(Achievement $achievement): void
    {
        $this->clearCache();
    }

    public function updated(Achievement $achievement): void
    {
        $this->clearCache();
    }

    public function deleted(Achievement $achievement): void
    {
        $this->clearCache();
    }

    public function restored(Achievement $achievement): void
    {
        $this->clearCache();
    }

    protected function clearCache(): void
    {
        Achievements::make()->regenerateCache();
    }
}
