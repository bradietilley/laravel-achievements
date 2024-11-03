<?php

namespace BradieTilley\Achievements\Listeners;

use BradieTilley\Achievements\Models\AchievementEvent;

class EventListener
{
    public function handle($event): void
    {
        AchievementEvent::byEvent($event)->each->check($event);
    }
}
