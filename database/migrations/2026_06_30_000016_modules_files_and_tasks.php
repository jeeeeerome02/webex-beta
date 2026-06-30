<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classroom_modules', function (Blueprint $table) {
            $table->json('files')->nullable()->after('description');
        });

        Schema::create('classroom_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->default('activity'); // quiz | activity | study | exam
            $table->text('description')->nullable();
            $table->string('deadline_type')->default('none'); // none | today | tomorrow | this_week | two_weeks | custom
            $table->timestamp('deadline_at')->nullable();
            $table->timestamp('deadline_end')->nullable();
            $table->string('duration')->nullable(); // 1h | 2h | 1w | 2w
            $table->string('visibility')->default('all'); // all | specific
            $table->json('visible_members')->nullable();
            $table->json('advanced')->nullable();
            $table->json('questions')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classroom_tasks');

        Schema::table('classroom_modules', function (Blueprint $table) {
            $table->dropColumn('files');
        });
    }
};
