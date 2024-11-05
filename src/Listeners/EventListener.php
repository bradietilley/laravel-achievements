<?php

namespace BradieTilley\Achievements\Listeners;

use BradieTilley\Achievements\Achievements;
use BradieTilley\Achievements\Jobs\ProcessAchievement;

class EventListener
{
    public function __call($name, $arguments)
    {
    }

    public function handle(string $event, array $payload): void
    {
        $user = Achievements::make()->user();

        if ($user === null) {
            return;
        }

        foreach (Achievements::byEvent($event) as $achievement) {
            if ($achievement->async) {
                ProcessAchievement::dispatch($achievement, $user, $event, null);
            } else {
                ProcessAchievement::dispatchSync($achievement, $user, $event, $payload);
            }
        }
    }
}
