<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classroom_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->boolean('comments_enabled')->default(true);
            $table->boolean('is_hidden')->default(false);
            $table->timestamps();
        });

        Schema::create('classroom_post_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('classroom_posts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['post_id', 'user_id']);
        });

        Schema::create('classroom_post_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('classroom_posts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classroom_post_comments');
        Schema::dropIfExists('classroom_post_likes');
        Schema::dropIfExists('classroom_posts');
    }
};
