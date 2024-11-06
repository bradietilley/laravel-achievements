<?php

use BradieTilley\Achievements\Models\Achievement;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('user_achievement', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Achievement::alias(), 'achievement_id');
            $table->morphs('user');

            $table->timestamps();

            $table->unique([
                'achievement_id',
                'user_type',
                'user_id',
            ]);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_achievement');
    }
};
