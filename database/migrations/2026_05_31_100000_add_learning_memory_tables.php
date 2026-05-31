<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Learning memory — one per conversation, adaptive summary of student profile
        Schema::create('learning_memories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('summary')->nullable();
            $table->json('topics_seen')->nullable();
            $table->json('weak_areas')->nullable();
            $table->json('strengths')->nullable();
            $table->timestamps();
        });

        // Add model_used and is_adaptive to messages table
        Schema::table('messages', function (Blueprint $table) {
            $table->string('model_used', 20)->nullable()->after('role'); // 'chiedza'|'gpt'
        });

        // Add preferred_model and subject to conversations table
        Schema::table('conversations', function (Blueprint $table) {
            $table->string('preferred_model', 20)->default('auto')->after('channel');
            $table->string('subject', 100)->nullable()->after('preferred_model');
            $table->string('level', 50)->nullable()->after('subject'); // O-Level, A-Level, etc.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_memories');
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('model_used');
        });
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn(['preferred_model', 'subject', 'level']);
        });
    }
};
