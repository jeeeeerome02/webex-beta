<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('country')->nullable();
            $table->string('subject')->nullable();
        });

        Schema::table('classrooms', function (Blueprint $table) {
            $table->boolean('show_on_profile')->default(true);
        });

        Schema::table('classroom_members', function (Blueprint $table) {
            $table->boolean('show_on_profile')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['country', 'subject']);
        });

        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropColumn('show_on_profile');
        });

        Schema::table('classroom_members', function (Blueprint $table) {
            $table->dropColumn('show_on_profile');
        });
    }
};
