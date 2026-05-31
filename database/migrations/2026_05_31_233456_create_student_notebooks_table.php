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
        Schema::create('student_notebooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('conversation_id')->nullable()->constrained()->nullOnDelete();
            // 'notes' | 'study_plan' | 'advanced_plan'
            $table->enum('type', ['notes', 'study_plan', 'advanced_plan'])->default('notes');
            $table->string('title');
            $table->string('subject')->nullable();
            $table->string('level')->nullable();
            $table->string('topic')->nullable();
            // For notes: the markdown text. For plans: serialised JSON string.
            $table->longText('content');
            // Advanced plan extras — YouTube video list (JSON array)
            $table->json('youtube_videos')->nullable();
            // Tags for filtering
            $table->json('tags')->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'is_pinned']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_notebooks');
    }
};
