<?php

namespace BradieTilley\Achievements\Jobs;

use BradieTilley\Achievements\Contracts\EarnsAchievements;
use BradieTilley\Achievements\Models\Achievement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProcessAchievement implements ShouldQueue
{
    use Dispatchable;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Achievement $achievement,
        public readonly Model&EarnsAchievements $user,
        public readonly string $event,
        public readonly null|array $payload,
    ) {
    }

    public function handle(): void
    {
        $hasAchievement = $this->user->hasAchievement($this->achievement);

        if (! $this->achievement->reverseable && $hasAchievement) {
            return;
        }

        $eligible = $this->checkIsEligible();

        if ($eligible && ! $hasAchievement) {
            $this->user->giveAchievement($this->achievement);
        }

        if (! $eligible && $hasAchievement) {
            $this->user->revokeAchievement($this->achievement);
        }
    }

    public function checkIsEligible(): bool
    {
        foreach ($this->achievement->criteria as $criteria) {
            if (! $criteria->isEligible($this->achievement, $this->user, $this->event, $this->payload)) {
                return false;
            }
        }

        return true;
    }
}
