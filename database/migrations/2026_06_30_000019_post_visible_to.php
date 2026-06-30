<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classroom_posts', function (Blueprint $table) {
            // null = visible to everyone; array of user ids = only those members (plus teachers).
            $table->json('visible_to')->nullable()->after('kind');
        });
    }

    public function down(): void
    {
        Schema::table('classroom_posts', function (Blueprint $table) {
            $table->dropColumn('visible_to');
        });
    }
};
