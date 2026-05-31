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
        Schema::table('session_ai_reports', function (Blueprint $table) {
            // Gemini's raw note-taker output — stored before GPT-4o validation
            $table->mediumText('gemini_notes')->nullable()->after('transcript_excerpt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('session_ai_reports', function (Blueprint $table) {
            $table->dropColumn('gemini_notes');
        });
    }
};
