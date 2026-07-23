<?php

namespace BradieTilley\Achievements\Casts;

use BradieTilley\Achievements\AchievementsConfig;
use BradieTilley\Achievements\Criteria\Criteria;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * @implements CastsAttributes<array<int, Criteria>, array<int, Criteria>|null>
 */
class CriteriaSerializationCast implements CastsAttributes
{
    /**
     * @return array<int, Criteria>
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (! is_string($value)) {
            throw new InvalidArgumentException('Achievement criteria must be stored as a JSON string.');
        }

        /** @var array<int, array{class?: string, data?: array<string, mixed>}> $decoded */
        $decoded = json_decode($value, true, flags: JSON_THROW_ON_ERROR);

        return array_values(array_map(
            function (array $item): Criteria {
                $class = $item['class'] ?? null;
                $data = $item['data'] ?? [];

                if (! is_string($class) || ! is_a($class, Criteria::class, true)) {
                    throw new InvalidArgumentException('Invalid achievement criteria class.');
                }

                if (! AchievementsConfig::isCriteriaClassAllowed($class)) {
                    throw new InvalidArgumentException("Achievement criteria class [{$class}] is not allowlisted.");
                }

                return $class::fromArray($data);
            },
            $decoded,
        ));
    }

    /**
     * @param  array<int, Criteria>|null  $value
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        $criteria = array_values($value ?? []);

        return json_encode(
            array_map(
                function (Criteria $item): array {
                    $class = $item::class;

                    if (! AchievementsConfig::isCriteriaClassAllowed($class)) {
                        throw new InvalidArgumentException("Achievement criteria class [{$class}] is not allowlisted.");
                    }

                    return [
                        'class' => $class,
                        'data' => $item->toArray(),
                    ];
                },
                $criteria,
            ),
            JSON_THROW_ON_ERROR,
        );
    }
}
