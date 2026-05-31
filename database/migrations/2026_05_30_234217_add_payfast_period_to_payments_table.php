<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Add payfast to the provider enum
            DB::statement("ALTER TABLE payments MODIFY COLUMN provider ENUM('stripe','paynow_zw','ecocash','innbucks','manual','payfast') NOT NULL DEFAULT 'stripe'");
            // Access period chosen at checkout
            $table->enum('access_period', ['monthly', 'termly', 'annual', 'lifetime'])
                  ->default('lifetime')
                  ->after('provider');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('access_period');
            DB::statement("ALTER TABLE payments MODIFY COLUMN provider ENUM('stripe','paynow_zw','ecocash','innbucks','manual') NOT NULL DEFAULT 'stripe'");
        });
    }
};
