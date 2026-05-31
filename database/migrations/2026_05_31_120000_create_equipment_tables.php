<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Teacher Equipment Self-Declaration ────────────────────────────────
        Schema::create('teacher_equipment_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();

            // Internet
            $table->boolean('has_stable_internet')->default(false);
            $table->enum('internet_type', ['fibre', 'dsl', 'cable', 'mobile_4g', 'mobile_5g', 'satellite', 'other'])->nullable();
            $table->unsignedSmallInteger('internet_speed_mbps')->nullable()->comment('Self-reported download speed');

            // Device
            $table->boolean('has_laptop_or_desktop')->default(false);
            $table->enum('device_type', ['laptop', 'desktop', 'tablet'])->nullable();
            $table->string('device_os')->nullable()->comment('e.g. Windows 11, macOS Sonoma');

            // Camera
            $table->boolean('has_camera')->default(false);
            $table->enum('camera_type', ['built_in', 'external_webcam', 'phone'])->nullable();

            // Lighting
            $table->boolean('has_proper_lighting')->default(false);
            $table->enum('lighting_type', ['natural', 'ring_light', 'softbox', 'led_panel', 'desk_lamp', 'other'])->nullable();

            // Headset
            $table->boolean('has_noise_canceling_headset')->default(false);
            $table->string('headset_model')->nullable();

            // Overall status (computed summary)
            $table->enum('status', ['incomplete', 'meets_requirements', 'needs_improvement'])->default('incomplete');

            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique('teacher_id');
        });

        // ── Equipment Loan Applications ───────────────────────────────────────
        Schema::create('equipment_loan_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();

            // What they're applying for (array of item keys)
            $table->json('items_requested')->comment('e.g. ["laptop","webcam","headset"]');
            $table->text('purpose')->comment('Why they need these items');
            $table->decimal('amount_requested_usd', 8, 2);
            $table->unsignedTinyInteger('repayment_period_months')->default(12)->comment('Requested months 3–24');

            // Teacher's additional info
            $table->text('employment_context')->nullable()->comment('Course load, student count, expected earnings');
            $table->text('teacher_notes')->nullable();

            // Admin review
            $table->enum('status', [
                'pending', 'under_review', 'approved', 'rejected', 'disbursed', 'repaying', 'completed'
            ])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            // If approved
            $table->decimal('approved_amount_usd', 8, 2)->nullable();
            $table->unsignedTinyInteger('approved_months')->nullable();
            $table->decimal('monthly_repayment_usd', 8, 2)->nullable();
            $table->decimal('interest_rate_percent', 5, 2)->nullable()->default(0)->comment('0 = interest-free');
            $table->string('reference_number')->nullable()->unique();
            $table->timestamp('disbursed_at')->nullable();
            $table->date('repayment_starts_on')->nullable();
            $table->date('repayment_ends_on')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_loan_applications');
        Schema::dropIfExists('teacher_equipment_profiles');
    }
};
