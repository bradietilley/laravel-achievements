<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Workbench\App\Models\Post;
use Workbench\App\Models\User;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(User::class, 'user_id');

            $table->timestamps();
        });

        Schema::create('revisions', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Post::class, 'post_id');

            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();

            $table->integer('logins')->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
