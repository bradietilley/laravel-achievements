<?php

namespace BradieTilley\Achievements\Database\Factories;

use BradieTilley\Achievements\Models\Achievement;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\BradieTilley\Achievements\Models\Achievement>
 */
class AchievementFactory extends Factory
{
    protected $model = Achievement::class;

    public function definition()
    {
        return [
            'name' => ucwords($this->faker->words(mt_rand(4, 7), true)),
            'events' => [],
            'criteria' => [],
            'reverseable' => ! mt_rand(0, 1),
            'tier' => Arr::random([
                'bronze',
                'silver',
                'gold',
                'platinum',
                'diamond',
            ]),
        ];
    }

    public function name(string $name): static
    {
        return $this->state([
            'name' => $name,
        ]);
    }

    public function tier(string $tier): static
    {
        return $this->state([
            'tier' => $tier,
        ]);
    }

    public function criteria(array $criteria): static
    {
        return $this->state([
            'criteria' => $criteria,
        ]);
    }

    public function events(array $events): static
    {
        return $this->state([
            'events' => $events,
        ]);
    }

    public function reverseable(bool $reverseable = true): static
    {
        return $this->state([
            'reverseable' => $reverseable,
        ]);
    }

    public function irreverseable(): static
    {
        return $this->reverseable(false);
    }
}
