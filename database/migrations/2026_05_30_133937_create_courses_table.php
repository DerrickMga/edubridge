<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('subject');
            $table->string('grade_level', 20);
            $table->string('thumbnail')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->decimal('price_usd', 8, 2)->default(0);
            $table->decimal('price_zwg', 10, 2)->default(0);
            $table->string('youtube_playlist_id')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('courses'); }
};
