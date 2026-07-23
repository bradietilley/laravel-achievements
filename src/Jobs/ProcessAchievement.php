<?php

namespace BradieTilley\Achievements\Jobs;

use BradieTilley\Achievements\Contracts\EarnsAchievements;
use BradieTilley\Achievements\Models\Achievement;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\DeleteWhenMissingModels;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;

#[Tries(3)]
#[Timeout(60)]
#[DeleteWhenMissingModels]
class ProcessAchievement implements ShouldQueue
{
    use Queueable;

    /**
     * @param  ?array<mixed>  $payload
     */
    public function __construct(
        public readonly Achievement $achievement,
        public readonly Model&EarnsAchievements $user,
        public readonly string $event,
        public readonly ?array $payload,
        public readonly CarbonImmutable $now,
    ) {
        //
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
            if (! $criteria->isEligible($this->achievement, $this->user, $this->event, $this->payload, $this->now)) {
                return false;
            }
        }

        return true;
    }
}
