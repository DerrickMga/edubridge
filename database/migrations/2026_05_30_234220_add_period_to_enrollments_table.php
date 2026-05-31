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
        Schema::table('enrollments', function (Blueprint $table) {
            $table->enum('access_period', ['monthly', 'termly', 'annual', 'lifetime'])
                  ->default('lifetime')
                  ->after('status');
            $table->timestamp('expires_at')->nullable()->after('access_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn(['access_period', 'expires_at']);
        });
    }
};
