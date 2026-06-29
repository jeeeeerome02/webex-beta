<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classroom_members', function (Blueprint $table) {
            $table->boolean('is_co_teacher')->default(false)->after('status');
        });

        Schema::create('awards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('given_by')->constrained('users')->cascadeOnDelete();
            $table->string('label');
            $table->string('icon')->default('pi pi-star');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('awards');
        Schema::table('classroom_members', function (Blueprint $table) {
            $table->dropColumn('is_co_teacher');
        });
    }
};
