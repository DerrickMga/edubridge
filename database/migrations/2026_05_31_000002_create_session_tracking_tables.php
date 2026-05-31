<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('actual_duration_minutes');
            $table->unsignedSmallInteger('actual_student_count');
            $table->text('notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->unique('live_session_id');
        });

        Schema::create('session_ai_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quiz_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('attendees_count')->default(0);
            $table->text('summary')->nullable();
            $table->json('action_items')->nullable();
            $table->text('transcript_excerpt')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->unique('live_session_id');
        });

        Schema::create('teacher_payment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('live_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('session_log_id')->nullable()->constrained('session_logs')->nullOnDelete();
            $table->decimal('hours_logged', 5, 2)->default(0);
            $table->unsignedSmallInteger('student_count')->default(0);
            $table->decimal('hourly_rate_usd', 8, 2)->default(0);
            $table->decimal('total_usd', 10, 2)->default(0);
            $table->enum('status', ['pending', 'approved', 'paid'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->unique('live_session_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_payment_items');
        Schema::dropIfExists('session_ai_reports');
        Schema::dropIfExists('session_logs');
    }
};
