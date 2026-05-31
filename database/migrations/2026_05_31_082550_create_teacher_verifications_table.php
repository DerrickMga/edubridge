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
        Schema::create('teacher_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending | approved | rejected | needs_resubmission

            // Identity info
            $table->string('full_legal_name');
            $table->string('national_id_number');
            $table->string('id_document_front')->nullable();  // storage path
            $table->string('id_document_back')->nullable();
            $table->string('proof_of_qualification')->nullable(); // degree/cert scan
            $table->string('selfie_with_id')->nullable();

            // Payout details (stored at verification time)
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_branch_code')->nullable();
            $table->string('ecocash_number')->nullable();
            $table->string('innbucks_number')->nullable();
            $table->string('paynow_email')->nullable();

            // Admin review
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_verifications');
    }
};
