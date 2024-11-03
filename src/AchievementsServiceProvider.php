<?php

namespace BradieTilley\Achievements;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class AchievementsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('achievements')
            ->hasConfigFile()
            ->hasMigrations(
                'create_achievements_table',
                'create_reputations_table',
                'create_reputations_logs_table',
                'create_user_achievement_table',
            );
    }
}