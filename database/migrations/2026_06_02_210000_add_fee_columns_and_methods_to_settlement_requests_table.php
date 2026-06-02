<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('settlement_requests', function (Blueprint $table) {
            // Fee breakdown columns
            $table->decimal('settlement_fee_pct', 5, 2)->default(0)->after('amount_usd');
            $table->decimal('settlement_fee_usd', 10, 2)->default(0)->after('settlement_fee_pct');
            $table->decimal('net_amount_usd', 10, 2)->default(0)->after('settlement_fee_usd');

            // Expand payment_method to include new options (string, not enum, so just add a comment)
            // The payment_method column is already a string — new values: cash_token, omari, paystack
        });
    }

    public function down(): void
    {
        Schema::table('settlement_requests', function (Blueprint $table) {
            $table->dropColumn(['settlement_fee_pct', 'settlement_fee_usd', 'net_amount_usd']);
        });
    }
};
