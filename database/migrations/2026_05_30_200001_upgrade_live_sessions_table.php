<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite does not enforce enum constraints, so provider column accepts
        // any string already. We only add the new columns.
        Schema::table('live_sessions', function (Blueprint $table) {
            $table->boolean('is_recorded')->default(false);
            $table->boolean('breakout_rooms_enabled')->default(false);
            $table->unsignedSmallInteger('breakout_room_count')->default(0);
            $table->unsignedInteger('attendees_count')->default(0);
            $table->text('teacher_notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('live_sessions', function (Blueprint $table) {
            $table->dropColumn([
                'is_recorded','breakout_rooms_enabled','breakout_room_count',
                'attendees_count','teacher_notes',
            ]);
        });
    }
};
