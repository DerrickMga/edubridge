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
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('email');
            $table->text('bio')->nullable()->after('avatar');
            $table->string('website')->nullable()->after('bio');
            $table->string('linkedin_url')->nullable()->after('website');
            $table->string('twitter_handle')->nullable()->after('linkedin_url');
            $table->string('qualification')->nullable()->after('twitter_handle'); // teachers
            $table->string('city')->nullable()->after('country');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['avatar', 'bio', 'website', 'linkedin_url', 'twitter_handle', 'qualification', 'city']);
        });
    }
};
