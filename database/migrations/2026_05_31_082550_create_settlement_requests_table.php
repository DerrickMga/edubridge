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
        Schema::create('settlement_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount_usd', 10, 2);
            $table->string('payment_method'); // bank_transfer | ecocash | innbucks | paynow
            $table->json('payout_details');   // {account_name, account_number, bank_name, ...}
            $table->string('status')->default('pending'); // pending | approved | processing | paid | rejected
            $table->string('reference_number')->nullable(); // assigned when processed
            $table->text('teacher_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settlement_requests');
    }
};
