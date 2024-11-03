<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();

            $table->string('name')->unique();
            $table->boolean('reverseable');
            $table->string('tier')->index();
            $table->text('criteria');
            $table->text('events');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('achievements');
    }
};
