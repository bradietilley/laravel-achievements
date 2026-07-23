<?php

namespace BradieTilley\Achievements\Criteria;

use BradieTilley\Achievements\Contracts\EarnsAchievements;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

abstract class RelationCriteria extends Criteria
{
    /**
     * @param  array<string, mixed>|list<array{field: string, value: mixed, operator?: string}>  $where
     */
    public function __construct(
        public readonly string $relation,
        public readonly array $where = [],
    ) {
    }

    /**
     * Resolve the relation query for the user and apply where constraints.
     *
     * @return Relation<Model, Model, mixed>|null
     */
    protected function relationQuery(Model&EarnsAchievements $user): ?Relation
    {
        if (! method_exists($user, $this->relation)) {
            return null;
        }

        $relation = $user->{$this->relation}();

        if (! $relation instanceof Relation) {
            return null;
        }

        $this->applyWhere($relation, $this->where);

        return $relation;
    }

    /**
     * Apply where constraints to the relation query.
     *
     * Associative maps use equality (`['status' => 'published']`).
     * Lists of condition arrays support optional operators:
     * `[['field' => 'content', 'operator' => 'LIKE', 'value' => '%hi%']]`.
     *
     * @param  Relation<Model, Model, mixed>  $query
     * @param  array<string, mixed>|list<array{field: string, value: mixed, operator?: string}>  $where
     */
    protected function applyWhere(Relation $query, array $where): void
    {
        if ($where === []) {
            return;
        }

        if (array_is_list($where)) {
            foreach ($where as $condition) {
                if (! is_array($condition) || ! array_key_exists('field', $condition) || ! array_key_exists('value', $condition)) {
                    continue;
                }

                $field = $condition['field'];

                if (! is_string($field)) {
                    continue;
                }

                $value = $condition['value'];

                if (array_key_exists('operator', $condition) && is_string($condition['operator'])) {
                    $query->where($field, $condition['operator'], $value);

                    continue;
                }

                $query->where($field, $value);
            }

            return;
        }

        foreach ($where as $field => $value) {
            if (! is_string($field)) {
                continue;
            }

            $query->where($field, $value);
        }
    }
}
