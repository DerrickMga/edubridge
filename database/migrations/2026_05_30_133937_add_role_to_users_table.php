<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['student', 'teacher', 'admin'])->default('student')->after('email');
            $table->string('phone', 20)->nullable()->after('role');
            $table->string('country', 3)->default('ZW')->after('phone');
            $table->string('grade_level', 20)->nullable()->after('country');
            $table->boolean('is_active')->default(true)->after('grade_level');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'phone', 'country', 'grade_level', 'is_active']);
        });
    }
};
