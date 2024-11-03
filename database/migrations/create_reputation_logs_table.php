<?php

use BradieTilley\Achievements\Models\Reputation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('reputation_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Reputation::getConfiguredClass(), 'reputation_id');
            $table->nullableMorphs('user'); // user who performed the action
            $table->string('message')->nullable();
            $table->bigInteger('points')->index();

            $table->timestamp('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('reputation_logs');
    }
};
