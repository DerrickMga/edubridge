<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recordings', function (Blueprint $table) {
            $table->foreignId('lesson_id')->nullable()->after('course_id')
                ->constrained()->nullOnDelete();
            $table->string('youtube_video_id', 32)->nullable()->after('external_url');
            $table->string('youtube_url', 500)->nullable()->after('youtube_video_id');
            $table->string('youtube_status', 24)->default('idle')->after('youtube_url');
            $table->text('youtube_error')->nullable()->after('youtube_status');
            $table->string('youtube_privacy', 16)->default('unlisted')->after('youtube_error');
            $table->string('thumbnail_url', 500)->nullable()->after('youtube_privacy');
        });

        Schema::create('google_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('scope', 1024)->nullable();
            $table->text('access_token');
            $table->text('refresh_token')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('channel_id')->nullable();
            $table->string('channel_title')->nullable();
            $table->timestamps();
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_tokens');
        Schema::table('recordings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lesson_id');
            $table->dropColumn([
                'youtube_video_id', 'youtube_url', 'youtube_status',
                'youtube_error', 'youtube_privacy', 'thumbnail_url',
            ]);
        });
    }
};
