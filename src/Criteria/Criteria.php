<?php

namespace BradieTilley\Achievements\Criteria;

use BradieTilley\Achievements\Contracts\EarnsAchievements;
use BradieTilley\Achievements\Models\Achievement;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Fluent;
use JsonSerializable;
use ReflectionClass;
use ReflectionProperty;

/**
 * @implements Arrayable<string, mixed>
 */
abstract class Criteria implements Arrayable, Jsonable, JsonSerializable
{
    /**
     * @param null|array<mixed> $payload
     */
    abstract public function isEligible(Achievement $achievement, Model&EarnsAchievements $user, string $event, array|null $payload): bool;

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $reflection = new ReflectionClass($this);

        return Arr::map(
            $reflection->getProperties(ReflectionProperty::IS_PUBLIC),
            fn (ReflectionProperty $property) => $this->{$property->getName()},
        );
    }

    /**
     * Convert the fluent instance to JSON.
     *
     * @param  int  $options
     */
    public function toJson($options = 0): string
    {
        /** @phpstan-ignore-next-line */
        return json_encode($this->jsonSerialize(), $options);
    }

}
