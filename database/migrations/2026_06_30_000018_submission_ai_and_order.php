<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classroom_task_submissions', function (Blueprint $table) {
            $table->json('ai')->nullable()->after('logs'); // AI essay scores / feedback keyed by question index
            $table->json('order')->nullable()->after('ai'); // randomized question order for this attempt
        });
    }

    public function down(): void
    {
        Schema::table('classroom_task_submissions', function (Blueprint $table) {
            $table->dropColumn(['ai', 'order']);
        });
    }
};
