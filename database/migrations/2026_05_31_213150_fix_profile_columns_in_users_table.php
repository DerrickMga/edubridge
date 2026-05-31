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
            // country was VARCHAR(3) – store full names like "United Kingdom"
            $table->string('country', 100)->nullable()->default(null)->change();
            // phone was VARCHAR(20) – validation allows 30
            $table->string('phone', 30)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('country', 3)->nullable(false)->default('ZW')->change();
            $table->string('phone', 20)->nullable()->change();
        });
    }
};
