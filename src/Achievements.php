<?php

namespace BradieTilley\Achievements;

use BradieTilley\Achievements\Contracts\EarnsAchievements;
use BradieTilley\Achievements\Models\Achievement;
use Closure;
use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;

class Achievements
{
    public const CACHE_KEY_READY = 'bradietilley_achievements.ready';

    public const CACHE_KEY_ACHIEVEMENTS = 'bradietilley_achievements.achievements';

    public const CACHE_KEY_EVENTS = 'bradietilley_achievement.events';

    /** @var null|(Closure(): (Model&EarnsAchievements)|null) */
    protected static ?Closure $userResolver = null;

    protected Repository $cache;

    protected array $ignoredEvents = [
        \Illuminate\Console\Events\ArtisanStarting::class,
        \Illuminate\Database\Events\ConnectionEstablished::class,
        \Illuminate\Database\Events\ConnectionEvent::class,
        \Illuminate\Database\Events\DatabaseBusy::class,
        \Illuminate\Database\Events\DatabaseRefreshed::class,
        \Illuminate\Database\Events\MigrationEnded::class,
        \Illuminate\Database\Events\MigrationEvent::class,
        \Illuminate\Database\Events\MigrationsEnded::class,
        \Illuminate\Database\Events\MigrationsEvent::class,
        \Illuminate\Database\Events\MigrationsStarted::class,
        \Illuminate\Database\Events\MigrationStarted::class,
        \Illuminate\Database\Events\ModelPruningFinished::class,
        \Illuminate\Database\Events\ModelPruningStarting::class,
        \Illuminate\Database\Events\ModelsPruned::class,
        \Illuminate\Database\Events\NoPendingMigrations::class,
        \Illuminate\Database\Events\QueryExecuted::class,
        \Illuminate\Database\Events\SchemaDumped::class,
        \Illuminate\Database\Events\SchemaLoaded::class,
        \Illuminate\Database\Events\StatementPrepared::class,
        \Illuminate\Database\Events\TransactionBeginning::class,
        \Illuminate\Database\Events\TransactionCommitted::class,
        \Illuminate\Database\Events\TransactionCommitting::class,
        \Illuminate\Database\Events\TransactionRolledBack::class,
        \Illuminate\Cache\Events\CacheEvent::class,
        \Illuminate\Cache\Events\CacheHit::class,
        \Illuminate\Cache\Events\CacheMissed::class,
        \Illuminate\Cache\Events\ForgettingKey::class,
        \Illuminate\Cache\Events\KeyForgetFailed::class,
        \Illuminate\Cache\Events\KeyForgotten::class,
        \Illuminate\Cache\Events\KeyWriteFailed::class,
        \Illuminate\Cache\Events\KeyWritten::class,
        \Illuminate\Cache\Events\RetrievingKey::class,
        \Illuminate\Cache\Events\RetrievingManyKeys::class,
        \Illuminate\Cache\Events\WritingKey::class,
        \Illuminate\Cache\Events\WritingManyKeys::class,
    ];

    public function __construct(CacheManager $cache)
    {
        $this->cache = $cache->store();
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public function user(): (Model&EarnsAchievements)|null
    {
        static::$userResolver ??= function (): (Model&EarnsAchievements)|null {
            $user = Auth::user();

            if ($user instanceof Model && $user instanceof EarnsAchievements) {
                return $user;
            }

            return null;
        };

        return (static::$userResolver)();
    }

    public function regenerateCache(): void
    {
        $this->cache->put(static::CACHE_KEY_ACHIEVEMENTS, $achievements = Achievement::all()->collect());

        $events = $achievements->pluck('events')->collapse()->unique()->values()->all();

        $this->cache->put(static::CACHE_KEY_EVENTS, $events);
    }

    /**
     * @return Collection<int, Achievement>
     */
    public function getAchievements(): Collection
    {
        return $this->cache->remember(
            static::CACHE_KEY_ACHIEVEMENTS,
            now()->addHour(),
            fn () => Achievement::all()->collect(),
        );
    }

    /**
     * @return array<int, string>
     */
    public function getEvents(): array
    {
        return $this->cache->get(static::CACHE_KEY_EVENTS, []);
    }

    public function registerEventListener(): void
    {
        Event::listen('*', function (string $event, mixed $payload) {
            if (in_array($event, $this->getIgnoredEvents())) {
                return;
            }

            $listen = $this->getEvents();

            if (! in_array($event, $listen)) {
                return;
            }

            $class = AchievementsConfig::getListenerClass();

            $listener = (new $class());
            $listener->handle($event, $payload);
        });
    }

    /**
     * @return Collection<int, Achievement>
     */
    public static function byEvent(string $event): Collection
    {
        return static::make()
            ->getAchievements()
            ->filter(fn (Achievement $achievement) => in_array($event, $achievement->events))
            ->values();
    }

    public function getIgnoredEvents(): array
    {
        return $this->ignoredEvents;
    }
}
