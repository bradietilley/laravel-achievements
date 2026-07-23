<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('reputations', function (Blueprint $table) {
            $table->id();

            $table->string('user_type');
            $table->unsignedBigInteger('user_id');
            $table->unique(['user_type', 'user_id']);
            $table->bigInteger('points')->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reputations');
    }
};
