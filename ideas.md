<?php


/**
 * Achievements stored as models
 */

use BradieTilley\Achievements\Contracts\EarnsAchievements;
use BradieTilley\Achievements\Models\Achievement;
use Illuminate\Database\Eloquent\Model;

Achievement::create([
    'name' => 'Task Ninja',
    // 'criteria' => ...
]);

/**
 * Models cached for optimised reading
 */
Achievement::allCached(); 

/**
 * Achievements have a type of achievement, defining how it's tracked
 */
/**
 * For example:
 * - the user creates 10 posts
 * - the user has spent 10 hours on the app
 */
AchievementType::Counter;
/**
 * For example:
 * - the user visits on a specific day
 * - the user visits on a specific date + time
 * - the user visits on a specific time
 */
AchievementType::Date;
/**
 * For example:
 * - user is a good lad in the eyes of a super admin
 */
AchievementType::Manual;
/**
 * For example:
 * - user has performed a specific sequence of things in the current session.
 * - user does some things that require custom computation
 */
AchievementType::Bespoke;

/**
 * Achievements have one or more criteria, each including a defiend type.
 */
// Visit 25 pages on the 25th December would require AchievementType::Counter and AchievementType::Date

/**
 * Achievement Criteria stored as JSON in Achievement
 */
Achievement::create([
    'name' => 'X-mas Day 2024!',
    'criteria' => [
        $criteria1,
        $criteria2,
    ],
]);


/**
 * Criteria should be able to handle most basic achievements (Counter and Date)
 * where a handler shouldn't beed to be required.
 * Like: creating 10 posts, visiting on certain days, 
 */
new Criteria(
    type: AchievementType::Counter,
    key: 'posts',
    value: 10,
);
new RelationshipCriteria('posts', 10);
new Criteria(
    type: AchievementType::Date,
    key: 'Y-m-d',
    value: '2024-12-31',
);
new DateCriteria('2024-12-31', 'Y-m-d');
new Criteria(
    type: AchievementType::Bespoke,
    value: SomeCustomCriteria::class
);
new CustomCriteria(SomeCustomCriteria::class);
new Criteria(
    type: AchievementType::Manual,
);
new ManualCriteria();

/**
 * Some achievements might be reverseable or recalculatable, which
 * will be recalculatable
 */
Achievement::create([
    // ...
    'reverseable' => true,
]);

/**
 * Achievements will be tiered, by default bronze-platinum but configurable
 */
Achievement::create([
    'tier' => 'bronze',
]);

/**
 * Achievements must be able to run (or recalculate based on various events)
 * 
 * Events can be listened via listenTo()
 * Eloquent lifecycle events can be listened via listenToEloquent()
 * 
 * These will create AchievementEvent
 */
$achievement->listenTo(Authenticated::class)->listenToEloquent(User::class, 'created');
AchievementEvent::create([
    'achievement_id' => $achievement->id,
    'event' => Authenticated::class,
]);

/**
 * These events must be cached and easily mapped to listeners in a cached approach
 * that requires little-to-no computation each request life cycle.
 */
AchievementEvent::getCachedMap();
/**
 * [
 *     'Illuminate\...\Authenticated' => 'BradieTilley\Achievements\Listeners\AchievementListener',
 * ]
 */

/**
 * Events will be listened automatically using this map
 */
Event::listen(AchievementEvent::getCachedMap());

/**
 * AchievementListener will listen to any event, load all cached achievements by event type
 * and invoke them
 */
function handle($event)
{
    Achievement::byEvent($event)
        ->each(fn (Achievement $achievement) => $achievement->checkAchievement());
}

/**
 * Some Achievements might need to be checked asynchronously (checkAchievement -> run job)
 */
Achievement::create([
    'async' => true,
]);

/**
 * The CheckAchievement job will either run sync or async depending on the achievement.
 * 
 * Anything request-related should be sync
 */
CheckAchievement::dispatch($achievement, $user);
CheckAchievement::dispatchSync($achievement, $user);

/**
 * The checking of achievements will defer to the criteria handler class
 */
foreach ($achievement->criteria as $criteria) {
    if (false === $criteria->check()) {
        $this->achievement->revoke($this->user);
        
        return;
    }
}
$this->achievement->grant($this->user);

/**
 * User Achievements will be recorded in a model 
 */
function grant(Model&EarnsAchievements $user): void
{
    UserAchievement::create([
        'user_id' => $user->id,
        'achievement_id' => $this->id,
    ]);
}

function revoke(Model&EarnsAchievements $user): void
{
    if (! $this->reverseable) {
        return;
    }

    $this->detatch($user);
}

/**
 * Achievement Imagery will be categorised into one of a few configurable tiers. All
 * tiers will use the same image generator
 */
'image_generator' => AchievementImageGenerator::class,

/**
 * These generators will be able to render an image based on a given achievement, which
 * will contain necessary data (such as tier, name, criteria, etc) required to generate
 * the imagery. 
 * 
 * Handling of disks, caching, etc, will be handled by the image generator class.
 */
class AchievementImageGenerator extends AbstractImageGenerator
{
    public function __construct(public readonly Achievement $achievement)
    {
    }

    public function getSvgUrl(): string;
    
    public function getPngUrl(): string;

    public function getSvgPath(): string;

    public function getPngPath(): string;
}

/**
 * Fetching of the images can be done via the model, which proxy to the image generator.
 */
$achievement->getImageUrl();

/**
 * These two methods cache the result, preventing the need to create instances of
 * the AbstractImageGenerator classes.
 * 
 * @return array{url: string, path: string}
 */
function getImageData(): array
{
    return Cache::rememberForever("achievements.images.{$this->id}", function () {
        $generator = AchievementsConfig::getImageGenerator();
        $generator = new $generator($this);

        return [
            'url' => $generator->getSvgUrl(),
            'path' => $generator->getSvgPath(),
        ];
    });
}

/**
 * Imagery will be be generated in a single format by default, depending on the config
 * and all URLs and paths will point to the generated files based on that type.
 */
function getImageUrl(): string
{
    return $this->getImageData()['url'];
}

/**
 * Format configuration would be configurable, however it's ultimately up to the Image
 * Generator class and how it uses the following config.
 */
'image_generator_format' => 'svg',


/**
 * Achievements must be unique by name
 * 
 * You can check to see if a user has an achievement:
 */
$user->hasAchievement('First Contribution');
$user->hasAchievement($achievement);

/**
 * Can be granted by name or model
 */
$user->grantAchievement('First Contribution');
$user->grantAchievement($achievement);

/**
 * Can be revoked by name or model
 */
$user->revokeAchievement('First Contribution');
$user->revokeAchievement($achievement);





/**
 * Reputation / Points
 */

/**
 * Users will have points, not stored in the users table (as this requires migrations specific to
 * the models that implement the contract, whereas this should be powered by the contract/trait).
 * 
 * All point data to be stored in reputation model
 */
$user->reputation->points;






