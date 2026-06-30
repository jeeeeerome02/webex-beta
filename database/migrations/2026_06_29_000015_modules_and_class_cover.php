<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            $table->string('cover_image')->nullable();
        });

        Schema::table('classroom_posts', function (Blueprint $table) {
            $table->string('kind')->default('post'); // post | award | module | task
        });

        Schema::create('classroom_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('description');
            $table->string('file_url')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_mime')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classroom_modules');

        Schema::table('classroom_posts', function (Blueprint $table) {
            $table->dropColumn('kind');
        });

        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropColumn('cover_image');
        });
    }
};
