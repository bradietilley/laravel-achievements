<?php

namespace BradieTilley\Achievements;

use BradieTilley\Achievements\Contracts\EarnsAchievements;
use BradieTilley\Achievements\Models\Achievement;
use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Bus\Dispatcher;
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

    protected Repository $cache;

    /**
     * A list of ignored events, some irrelevant, some to prevent recursion
     */
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

    public function __construct(CacheManager $cache, protected Dispatcher $bus)
    {
        $this->cache = $cache->store();
    }

    /**
     * Resolve the Achievements singleton
     */
    public static function make(): static
    {
        return app(static::class);
    }

    /**
     * Get the current user, if available.
     */
    public function user(): (Model&EarnsAchievements)|null
    {
        $user = Auth::user();

        if ($user instanceof Model && $user instanceof EarnsAchievements) {
            return $user;
        }

        return null;
    }

    /**
     * Regenerate the cache after adding or updating achievements
     */
    public function regenerateCache(): void
    {
        $this->cache->put(static::CACHE_KEY_ACHIEVEMENTS, $achievements = Achievement::all()->collect());

        $events = $achievements->pluck('events')->collapse()->unique()->values()->all();

        $this->cache->put(static::CACHE_KEY_EVENTS, $events);
    }

    /**
     * Get all achievements (cached)
     *
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
     * Get all events that are leveraged by achievements
     *
     * @return array<int, string>
     */
    public function getEvents(): array
    {
        return $this->cache->get(static::CACHE_KEY_EVENTS, []);
    }

    /**
     * Register the wildcard event listener.
     *
     * Listen to every event, filter out only the events that are
     * currently observed by achievements, then handle the processing
     * of the event within the Event Listener class.
     */
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

            static::handleEvent($event, $payload);
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

    public function handleEvent(string $event, array $payload): void
    {
        $user = Achievements::make()->user();

        if ($user === null) {
            return;
        }

        $job = AchievementsConfig::getProcessAchievementJob();

        foreach (Achievements::byEvent($event) as $achievement) {
            if ($achievement->async) {
                $this->bus->dispatch(new $job($achievement, $user, $event, null));

                continue;
            }

            $this->bus->dispatchSync(new $job($achievement, $user, $event, $payload));
        }
    }
}
