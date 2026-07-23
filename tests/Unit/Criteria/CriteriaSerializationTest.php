<?php

use BradieTilley\Achievements\Criteria\CurrentDateCriteria;
use BradieTilley\Achievements\Criteria\DateBetweenCriteria;
use BradieTilley\Achievements\Criteria\DayOfWeekCriteria;
use BradieTilley\Achievements\Criteria\FieldCompareCriteria;
use BradieTilley\Achievements\Criteria\FieldInCriteria;
use BradieTilley\Achievements\Criteria\FieldIsNullCriteria;
use BradieTilley\Achievements\Criteria\HasRelationCountCriteria;
use BradieTilley\Achievements\Criteria\HasRelationExistsCriteria;
use BradieTilley\Achievements\Criteria\HasRelationSumCriteria;
use BradieTilley\Achievements\Criteria\MinimumPointsCriteria;
use BradieTilley\Achievements\Criteria\MonthCriteria;
use BradieTilley\Achievements\Criteria\TimeOfDayCriteria;
use BradieTilley\Achievements\Enums\ComparisonOperator;
use BradieTilley\Achievements\Models\Achievement;
use Illuminate\Support\Facades\DB;
use Tests\Support\DisallowedTestCriteria;

test('criteria can be converted to and from arrays with named keys', function () {
    $criteria = new MinimumPointsCriteria(100);

    expect($criteria->toArray())->toBe([
        'points' => 100,
    ]);

    expect(MinimumPointsCriteria::fromArray(['points' => 250]))
        ->toBeInstanceOf(MinimumPointsCriteria::class)
        ->points->toBe(250);
});

test('field compare criteria round-trips ComparisonOperator through arrays', function () {
    $criteria = new FieldCompareCriteria('logins', ComparisonOperator::GreaterThanOrEqual, 10);

    expect($criteria->toArray())->toBe([
        'field' => 'logins',
        'operator' => '>=',
        'value' => 10,
    ]);

    $restored = FieldCompareCriteria::fromArray([
        'field' => 'logins',
        'operator' => '>=',
        'value' => 10,
    ]);

    expect($restored)
        ->toBeInstanceOf(FieldCompareCriteria::class)
        ->field->toBe('logins')
        ->operator->toBe(ComparisonOperator::GreaterThanOrEqual)
        ->value->toBe(10);
});

test('relation criteria with where round-trip through arrays', function () {
    $where = [
        ['field' => 'content', 'operator' => 'LIKE', 'value' => '%happy%'],
    ];

    $count = new HasRelationCountCriteria('posts', 3, $where);
    expect($count->toArray())->toBe([
        'count' => 3,
        'relation' => 'posts',
        'where' => $where,
    ]);
    expect(HasRelationCountCriteria::fromArray($count->toArray()))
        ->toBeInstanceOf(HasRelationCountCriteria::class)
        ->count->toBe(3)
        ->relation->toBe('posts')
        ->where->toBe($where);

    $exists = new HasRelationExistsCriteria('posts', false, ['content' => 'published']);
    expect(HasRelationExistsCriteria::fromArray($exists->toArray()))
        ->exists->toBeFalse()
        ->where->toBe(['content' => 'published']);

    $sum = new HasRelationSumCriteria('posts', 'views', 100.5, $where);
    expect(HasRelationSumCriteria::fromArray($sum->toArray()))
        ->column->toBe('views')
        ->amount->toBe(100.5)
        ->where->toBe($where);
});

test('new field and calendar criteria round-trip through arrays', function () {
    expect(FieldInCriteria::fromArray([
        'field' => 'role',
        'values' => ['admin', 'editor'],
        'in' => false,
    ]))->field->toBe('role')->values->toBe(['admin', 'editor'])->in->toBeFalse();

    expect(FieldIsNullCriteria::fromArray([
        'field' => 'deleted_at',
        'null' => false,
    ]))->field->toBe('deleted_at')->null->toBeFalse();

    expect(DateBetweenCriteria::fromArray([
        'from' => '2024-01-01',
        'to' => '2024-12-31',
    ]))->from->toBe('2024-01-01')->to->toBe('2024-12-31');

    expect(DayOfWeekCriteria::fromArray([
        'days' => [0, 6],
    ]))->days->toBe([0, 6]);

    expect(MonthCriteria::fromArray([
        'months' => [12],
    ]))->months->toBe([12]);

    expect(TimeOfDayCriteria::fromArray([
        'from' => '09:00',
        'to' => '17:00',
    ]))->from->toBe('09:00')->to->toBe('17:00');
});

test('achievement criteria are persisted as JSON class payloads', function () {
    $achievement = Achievement::factory()
        ->criteria([
            new MinimumPointsCriteria(100),
            new CurrentDateCriteria('2024-06-06'),
            new HasRelationCountCriteria('posts', 1, [
                ['field' => 'content', 'operator' => 'LIKE', 'value' => '%hi%'],
            ]),
        ])
        ->createOne();

    $raw = $achievement->getAttributes()['criteria'];

    expect($raw)->toBeString()
        ->and(json_decode($raw, true))->toBe([
            [
                'class' => MinimumPointsCriteria::class,
                'data' => ['points' => 100],
            ],
            [
                'class' => CurrentDateCriteria::class,
                'data' => ['date' => '2024-06-06'],
            ],
            [
                'class' => HasRelationCountCriteria::class,
                'data' => [
                    'count' => 1,
                    'relation' => 'posts',
                    'where' => [
                        ['field' => 'content', 'operator' => 'LIKE', 'value' => '%hi%'],
                    ],
                ],
            ],
        ]);

    $reloaded = Achievement::query()->findOrFail($achievement->getKey());

    expect($reloaded->criteria)->toHaveCount(3)
        ->and($reloaded->criteria[0])->toBeInstanceOf(MinimumPointsCriteria::class)
        ->and($reloaded->criteria[0]->points)->toBe(100)
        ->and($reloaded->criteria[1])->toBeInstanceOf(CurrentDateCriteria::class)
        ->and($reloaded->criteria[1]->date)->toBe('2024-06-06')
        ->and($reloaded->criteria[2])->toBeInstanceOf(HasRelationCountCriteria::class)
        ->and($reloaded->criteria[2]->where)->toBe([
            ['field' => 'content', 'operator' => 'LIKE', 'value' => '%hi%'],
        ]);
});

test('non-allowlisted criteria classes are rejected when reading', function () {
    $achievement = Achievement::factory()->criteria([])->createOne();

    $payload = json_encode([
        [
            'class' => DisallowedTestCriteria::class,
            'data' => [],
        ],
    ], JSON_THROW_ON_ERROR);

    DB::table('achievements')->where('id', $achievement->getKey())->update([
        'criteria' => $payload,
    ]);

    $achievement = Achievement::query()->findOrFail($achievement->getKey());

    expect(fn () => $achievement->criteria)
        ->toThrow(InvalidArgumentException::class, 'is not allowlisted');
});
