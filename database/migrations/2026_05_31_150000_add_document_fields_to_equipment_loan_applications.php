<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment_loan_applications', function (Blueprint $table) {
            // Financial verification documents (stored on private disk)
            $table->string('income_proof_path')->nullable()
                ->comment('Payslip, bank statement, or employer letter – stored privately')
                ->after('teacher_notes');

            $table->string('address_proof_path')->nullable()
                ->comment('Utility bill, bank letter, or council tax document')
                ->after('income_proof_path');

            // Cheaper alternative quotation from teacher
            $table->string('quotation_path')->nullable()
                ->comment('Supplier quote if teacher found a cheaper alternative')
                ->after('address_proof_path');

            $table->text('quotation_notes')->nullable()
                ->comment('Details about the alternative quotation / supplier')
                ->after('quotation_path');
        });
    }

    public function down(): void
    {
        Schema::table('equipment_loan_applications', function (Blueprint $table) {
            $table->dropColumn(['income_proof_path', 'address_proof_path', 'quotation_path', 'quotation_notes']);
        });
    }
};
