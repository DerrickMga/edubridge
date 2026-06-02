<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('course_teacher', function (Blueprint $table) {
            $table->json('preferred_topics')->nullable()->after('hourly_rate_usd')
                  ->comment('JSON array of lesson IDs this teacher prefers to deliver');
            $table->text('notes')->nullable()->after('preferred_topics')
                  ->comment('Teacher notes on their involvement in this course');
        });
    }

    public function down(): void
    {
        Schema::table('course_teacher', function (Blueprint $table) {
            $table->dropColumn(['preferred_topics', 'notes']);
        });
    }
};
