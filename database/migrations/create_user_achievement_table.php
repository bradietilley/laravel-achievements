<?php

use BradieTilley\Achievements\AchievementsConfig;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('user_achievement', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(AchievementsConfig::getAchievementModel(), 'achievement_id');
            $table->morphs('model');

            $table->timestamps();

            $table->unique([
                'achievement_id',
                'model_type',
                'model_id',
            ]);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_achievement');
    }
};
