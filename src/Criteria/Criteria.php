<?php

namespace BradieTilley\Achievements\Criteria;

use BackedEnum;
use BradieTilley\Achievements\Contracts\EarnsAchievements;
use BradieTilley\Achievements\Models\Achievement;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\Model;
use JsonSerializable;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use UnitEnum;

/**
 * @implements Arrayable<string, mixed>
 */
abstract class Criteria implements Arrayable, Jsonable, JsonSerializable
{
    /**
     * @param  ?array<mixed>  $payload
     */
    abstract public function isEligible(Achievement $achievement, Model&EarnsAchievements $user, string $event, ?array $payload, CarbonImmutable $now): bool;

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static
    {
        $class = static::class;
        $reflection = new ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return new $class();
        }

        $args = [];

        foreach ($constructor->getParameters() as $parameter) {
            $name = $parameter->getName();

            if (! array_key_exists($name, $data)) {
                continue;
            }

            $args[$name] = static::castConstructorValue($parameter->getType(), $data[$name]);
        }

        return new $class(...$args);
    }

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
        $data = [];

        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $value = $this->{$property->getName()};

            if ($value instanceof BackedEnum) {
                $value = $value->value;
            } elseif ($value instanceof UnitEnum) {
                $value = $value->name;
            }

            $data[$property->getName()] = $value;
        }

        return $data;
    }

    /**
     * Convert the fluent instance to JSON.
     *
     * @param  int  $options
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options | JSON_THROW_ON_ERROR);
    }

    protected static function castConstructorValue(mixed $type, mixed $value): mixed
    {
        if (! $type instanceof ReflectionNamedType || $type->isBuiltin() || ! is_string($value)) {
            return $value;
        }

        $class = $type->getName();

        if (! enum_exists($class)) {
            return $value;
        }

        if (is_a($class, BackedEnum::class, true)) {
            return $class::from($value);
        }

        return constant("{$class}::{$value}");
    }
}
