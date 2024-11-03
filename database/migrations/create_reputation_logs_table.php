<?php

use BradieTilley\Achievements\AchievementsConfig;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('reputation_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(AchievementsConfig::getReputationModel(), 'reputation_id');
            $table->morphs('user');
            $table->bigInteger('points')->index();

            $table->timestamp('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('reputation_logs');
    }
};
