<?php

namespace BradieTilley\Achievements\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * @implements CastsAttributes<array<int,mixed>, string>
 */
class CriteriaSerializationCast implements CastsAttributes
{
    /**
     * @return array<int,mixed>
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        /** @phpstan-ignore-next-line */
        return unserialize($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        return serialize($value);
    }
}
