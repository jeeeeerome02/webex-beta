<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar_url')->nullable()->after('course');
            $table->string('cover_url')->nullable()->after('avatar_url');
        });

        Schema::table('classroom_posts', function (Blueprint $table) {
            $table->boolean('is_pinned')->default(false)->after('is_hidden');
        });

        Schema::create('friendships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('friend_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('accepted');
            $table->timestamps();
            $table->unique(['user_id', 'friend_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('friendships');
        Schema::table('classroom_posts', function (Blueprint $table) {
            $table->dropColumn('is_pinned');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['avatar_url', 'cover_url']);
        });
    }
};
