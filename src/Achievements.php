<?php

namespace BradieTilley\Achievements;

use BradieTilley\Achievements\Contracts\EarnsAchievements;
use BradieTilley\Achievements\Events\AchievementGranted;
use BradieTilley\Achievements\Events\AchievementRevoked;
use BradieTilley\Achievements\Models\Achievement;
use BradieTilley\Achievements\Models\UserAchievement;
use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Events\Dispatcher as EventsDispatcher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;

class Achievements
{
    public const CACHE_KEY_READY = 'bradietilley_achievements.ready';

    public const CACHE_KEY_ACHIEVEMENTS = 'bradietilley_achievements.achievements';

    public const CACHE_KEY_ACHIEVEMENT = 'bradietilley_achievements.achievement.';

    public const CACHE_KEY_EVENTS = 'bradietilley_achievement.events';

    /**
     * A list of ignored events, some irrelevant, some to prevent recursion
     *
     * @var array<int,string>
     */
    public const DEFAULT_IGNORED_EVENTS = [
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

    protected Repository $cache;

    public function __construct(
        protected BusDispatcher $bus,
        protected EventsDispatcher $events,
        CacheManager $cache,
    ) {
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
     * Regenerate the cache after adding or updating achievements.
     */
    public function regenerateCache(): void
    {
        $this->cache->put(static::CACHE_KEY_ACHIEVEMENTS, $achievements = Achievement::all()->collect());

        $events = $achievements->pluck('events')->collapse()->unique()->values()->all();

        $this->cache->put(static::CACHE_KEY_EVENTS, $events);
    }

    /**
     * Get all achievements (cached).
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
     * Get the achievement by name.
     */
    public function getAchievement(string|Achievement $achievement): Achievement
    {
        $class = Achievement::alias();
        $name = $achievement instanceof Achievement ? $achievement->name : $achievement;

        if ($achievement instanceof Achievement && $achievement::class === $class) {
            return $achievement;
        }

        return $this->cache->remember(
            static::CACHE_KEY_ACHIEVEMENT.$name,
            now()->addHour(),
            function () use ($class, $name): Achievement {
                return $class::query()->where('name', $name)->firstOrFail();
            },
        );
    }

    /**
     * Get all events that are leveraged by achievements.
     *
     * @return array<int, string>
     */
    public function getEvents(): array
    {
        /** @phpstan-ignore-next-line */
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
        Event::listen('*', function (string $event, array $payload) {
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

    /**
     * Get a list of all events to ignore.
     *
     * @return array<int,string>
     */
    public function getIgnoredEvents(): array
    {
        return static::DEFAULT_IGNORED_EVENTS;
    }

    /**
     * Handle the inound event (which is a non-ignored event).
     *
     * @param array<int,string> $payload
     */
    public function handleEvent(string $event, array $payload): void
    {
        $user = $this->user();

        if ($user === null) {
            return;
        }

        $job = AchievementsConfig::getProcessAchievementJob();

        foreach (static::byEvent($event) as $achievement) {
            if ($achievement->async) {
                $this->bus->dispatch(new $job($achievement, $user, $event, null));

                continue;
            }

            $this->bus->dispatchSync(new $job($achievement, $user, $event, $payload));
        }
    }

    /**
     * Give the given achievement to the given user.
     */
    public function giveAchievement(Achievement $achievement, Model&EarnsAchievements $user): void
    {
        try {
            $userAchievement = UserAchievement::alias();
            $userAchievement = new $userAchievement([
                'user_type' => $user->getMorphClass(),
                'user_id' => $user->getKey(),
                'achievement_id' => $achievement->getKey(),
            ]);

            $achievement->userAchievements()->save($userAchievement);

            $event = AchievementGranted::alias();
            $this->events->dispatch(new $event($achievement, $user));
        } catch (QueryException $e) {
            if (str_contains($e->getMessage(), 'UNIQUE constraint failed')) {
                return;
            }

            throw $e;
        }
    }

    /**
     * Revoke the given achievement from the given user.
     *
     * Only if the Achievement is reverseable (and/or if forcefully revoked).
     */
    public function revokeAchievement(Achievement $achievement, Model&EarnsAchievements $user, bool $force = false): void
    {
        if ($achievement->reverseable || $force) {
            $existing = $achievement->userAchievements()->whereMorphedTo('user', $user)->first();

            if ($existing) {
                $existing->delete();

                $event = AchievementRevoked::alias();
                $this->events->dispatch(new $event($achievement, $user));
            }
        }
    }
}
