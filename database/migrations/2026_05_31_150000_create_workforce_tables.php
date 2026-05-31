<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Presence / availability columns on users
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_seen_at')->nullable()->after('is_active');
            $table->string('availability_status', 16)->default('offline')->after('last_seen_at'); // online|busy|away|offline
            $table->boolean('accepts_assignments')->default(true)->after('availability_status');
            $table->string('timezone', 64)->default('UTC')->after('accepts_assignments');
        });

        // Course ↔ Teacher many-to-many (a course can have multiple teachers / co-teachers)
        Schema::create('course_teacher', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('role', 24)->default('co_teacher'); // primary|co_teacher|substitute
            $table->decimal('hourly_rate_usd', 8, 2)->nullable(); // override of users.hourly_rate_usd
            $table->timestamps();
            $table->unique(['course_id', 'teacher_id']);
            $table->index(['teacher_id', 'role']);
        });

        // Recurring weekly availability windows (in user's timezone)
        Schema::create('teacher_availability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 0=Sun..6=Sat
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
            $table->index(['teacher_id', 'day_of_week']);
        });

        // One-off unavailability
        Schema::create('teacher_time_off', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->string('reason', 255)->nullable();
            $table->string('status', 16)->default('approved'); // requested|approved|denied
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['teacher_id', 'starts_at']);
        });

        // Shifts — the rota unit. May or may not be tied to a live session.
        Schema::create('teacher_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('live_session_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 200);
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->string('status', 20)->default('scheduled'); // scheduled|in_progress|completed|missed|cancelled
            $table->boolean('auto_assigned')->default(false);
            $table->decimal('hourly_rate_usd_snapshot', 8, 2)->nullable();
            $table->decimal('hours_worked', 6, 2)->nullable();
            $table->decimal('payout_amount_usd', 10, 2)->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['teacher_id', 'starts_at']);
            $table->index(['status', 'starts_at']);
            $table->index('live_session_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_shifts');
        Schema::dropIfExists('teacher_time_off');
        Schema::dropIfExists('teacher_availability');
        Schema::dropIfExists('course_teacher');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_seen_at', 'availability_status', 'accepts_assignments', 'timezone']);
        });
    }
};
