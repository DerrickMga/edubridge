<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('xp')->default(0);
            $table->unsignedTinyInteger('level')->default(1);
            $table->unsignedSmallInteger('streak_days')->default(0);
            $table->unsignedSmallInteger('longest_streak')->default(0);
            $table->date('last_active_date')->nullable();
            $table->timestamps();
        });

        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description');
            $table->string('icon'); // emoji
            $table->string('colour')->default('amber'); // tailwind colour name
            $table->timestamps();
        });

        Schema::create('student_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('badge_id')->constrained()->cascadeOnDelete();
            $table->timestamp('earned_at');
            $table->timestamps();
            $table->unique(['user_id', 'badge_id']);
        });

        Schema::create('xp_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('event_type'); // lesson_complete, quiz_pass, streak, course_complete, etc.
            $table->unsignedSmallInteger('xp')->default(0);
            $table->string('description');
            $table->nullableMorphs('reference');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('xp_events');
        Schema::dropIfExists('student_badges');
        Schema::dropIfExists('badges');
        Schema::dropIfExists('student_stats');
    }
};
