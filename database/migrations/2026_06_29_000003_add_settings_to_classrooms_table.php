<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            $table->boolean('join_approval')->default(false)->after('type');
            $table->boolean('leave_approval')->default(false)->after('join_approval');
            $table->boolean('allow_posts')->default(true)->after('leave_approval');
        });
    }

    public function down(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropColumn(['join_approval', 'leave_approval', 'allow_posts']);
        });
    }
};
