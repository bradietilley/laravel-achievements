<?php

namespace BradieTilley\Achievements;

use BradieTilley\Achievements\Contracts\EarnsAchievements;
use BradieTilley\Achievements\Events\AchievementGranted;
use BradieTilley\Achievements\Events\AchievementRevoked;
use BradieTilley\Achievements\Models\Achievement;
use BradieTilley\Achievements\Models\UserAchievement;
use Carbon\CarbonImmutable;
use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Events\Dispatcher as EventsDispatcher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Collection;

class Achievements
{
    public const CACHE_KEY_ACHIEVEMENTS = 'bradietilley_achievements.achievements';

    public const CACHE_KEY_EVENTS = 'bradietilley_achievements.events';

    public const CACHE_TTL_SECONDS = 3600;

    /**
     * A list of ignored events, some irrelevant, some to prevent recursion
     *
     * @var array<int, string>
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
        \Illuminate\Queue\Events\JobExceptionOccurred::class,
        \Illuminate\Queue\Events\JobFailed::class,
        \Illuminate\Queue\Events\JobProcessed::class,
        \Illuminate\Queue\Events\JobProcessing::class,
        \Illuminate\Queue\Events\JobQueued::class,
        \Illuminate\Queue\Events\JobQueueing::class,
        \Illuminate\Queue\Events\JobReleasedAfterException::class,
        \Illuminate\Queue\Events\JobRetryRequested::class,
        \Illuminate\Queue\Events\JobTimedOut::class,
        \Illuminate\Queue\Events\Looping::class,
        \Illuminate\Queue\Events\QueueBusy::class,
        \Illuminate\Queue\Events\WorkerStopping::class,
        \Illuminate\Mail\Events\MessageSending::class,
        \Illuminate\Mail\Events\MessageSent::class,
        \Illuminate\Notifications\Events\NotificationFailed::class,
        \Illuminate\Notifications\Events\NotificationSent::class,
        \Illuminate\Notifications\Events\NotificationSending::class,
        \Illuminate\Routing\Events\PreparingResponse::class,
        \Illuminate\Routing\Events\ResponsePrepared::class,
        \Illuminate\Routing\Events\RouteMatched::class,
        \Illuminate\Routing\Events\Routing::class,
        \Illuminate\Http\Client\Events\ConnectionFailed::class,
        \Illuminate\Http\Client\Events\RequestSending::class,
        \Illuminate\Http\Client\Events\ResponseReceived::class,
    ];

    protected Repository $cache;

    public function __construct(
        protected BusDispatcher $bus,
        protected EventsDispatcher $events,
        CacheManager $cache,
        protected AuthFactory $auth,
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
        $user = $this->auth->guard()->user();

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
        $achievements = $this->getLiveAchievements();
        $events = $this->getLiveEvents($achievements);

        $this->cache->put(self::CACHE_KEY_ACHIEVEMENTS, $achievements, self::CACHE_TTL_SECONDS);
        $this->cache->put(self::CACHE_KEY_EVENTS, $events, self::CACHE_TTL_SECONDS);
    }

    /**
     * Forget cached achievement metadata.
     */
    public function forgetCache(): void
    {
        $this->cache->forget(self::CACHE_KEY_ACHIEVEMENTS);
        $this->cache->forget(self::CACHE_KEY_EVENTS);
    }

    /**
     * Get all achievements (cached).
     *
     * @return Collection<int, Achievement>
     */
    public function getAchievements(): Collection
    {
        /** @var Collection<int, Achievement>|null $achievements */
        $achievements = $this->cache->get(self::CACHE_KEY_ACHIEVEMENTS);

        if ($achievements !== null) {
            $this->cache->touch(self::CACHE_KEY_ACHIEVEMENTS, self::CACHE_TTL_SECONDS);

            return $achievements;
        }

        $achievements = $this->getLiveAchievements();
        $this->cache->put(self::CACHE_KEY_ACHIEVEMENTS, $achievements, self::CACHE_TTL_SECONDS);

        return $achievements;
    }

    /**
     * @return Collection<int, Achievement>
     */
    protected function getLiveAchievements(): Collection
    {
        return Achievement::alias()::query()->get();
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

        $cached = $this->getAchievements()->first(
            fn (Achievement $item): bool => $item->name === $name,
        );

        if ($cached !== null) {
            return $cached;
        }

        return $class::query()->where('name', $name)->firstOrFail();
    }

    /**
     * Get all events that are leveraged by achievements.
     *
     * @return array<int, string>
     */
    public function getEvents(): array
    {
        /** @var array<int, string>|null $events */
        $events = $this->cache->get(self::CACHE_KEY_EVENTS);

        if ($events !== null) {
            $this->cache->touch(self::CACHE_KEY_EVENTS, self::CACHE_TTL_SECONDS);

            return $events;
        }

        $events = $this->getLiveEvents();
        $this->cache->put(self::CACHE_KEY_EVENTS, $events, self::CACHE_TTL_SECONDS);

        return $events;
    }

    /**
     * @param  Collection<int, Achievement>|null  $achievements
     * @return array<int, string>
     */
    protected function getLiveEvents(?Collection $achievements = null): array
    {
        $events = [];

        foreach (($achievements ?? $this->getLiveAchievements()) as $achievement) {
            foreach ($achievement->events as $event) {
                $events[] = $event;
            }
        }

        return array_values(array_unique($events));
    }

    /**
     * Register the event listener.
     *
     * Achievements can listen to arbitrary event class names that are
     * stored in the database, so a wildcard listener is used and then
     * filtered against the cached list of relevant events.
     *
     * The listener only reads from cache so it never boots models while
     * Laravel is still booting models or running migrations.
     */
    public function registerEventListener(): void
    {
        $ignored = array_flip($this->getIgnoredEvents());

        $this->events->listen('*', function (string $event, array $payload) use ($ignored): void {
            if (isset($ignored[$event])) {
                return;
            }

            /** @var array<int, string> $listen */
            $listen = $this->cache->get(self::CACHE_KEY_EVENTS) ?? [];

            if ($listen === [] || ! in_array($event, $listen, true)) {
                return;
            }

            $this->handleEvent($event, $payload);
        });
    }

    /**
     * @return Collection<int, Achievement>
     */
    public static function byEvent(string $event): Collection
    {
        return static::make()
            ->getAchievements()
            ->filter(fn (Achievement $achievement) => in_array($event, $achievement->events, true))
            ->values();
    }

    /**
     * Get a list of all events to ignore.
     *
     * @return array<int, string>
     */
    public function getIgnoredEvents(): array
    {
        return static::DEFAULT_IGNORED_EVENTS;
    }

    /**
     * Handle the inbound event (which is a non-ignored event).
     *
     * @param  array<int|string, mixed>  $payload
     */
    public function handleEvent(string $event, array $payload): void
    {
        $user = $this->user();

        if ($user === null) {
            return;
        }

        $job = AchievementsConfig::getProcessAchievementJob();
        $now = CarbonImmutable::now();

        foreach (static::byEvent($event) as $achievement) {
            if ($achievement->async) {
                $this->bus->dispatch(new $job($achievement, $user, $event, $payload, $now));

                continue;
            }

            $this->bus->dispatchSync(new $job($achievement, $user, $event, $payload, $now));
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
        } catch (UniqueConstraintViolationException) {
            return;
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
