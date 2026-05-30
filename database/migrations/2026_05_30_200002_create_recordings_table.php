<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recordings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('storage_path')->nullable();
            $table->string('external_url')->nullable();
            $table->enum('source', ['zoom', 'google_meet', 'upload', 'youtube'])->default('upload');
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->unsignedBigInteger('file_size_bytes')->default(0);
            $table->enum('status', ['processing', 'available', 'failed'])->default('processing');
            $table->boolean('is_public')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('recordings'); }
};
