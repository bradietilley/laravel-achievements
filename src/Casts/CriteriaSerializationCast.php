<?php

namespace BradieTilley\Achievements\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class CriteriaSerializationCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes)
    {
        return unserialize($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes)
    {
        return serialize($value);
    }
}
