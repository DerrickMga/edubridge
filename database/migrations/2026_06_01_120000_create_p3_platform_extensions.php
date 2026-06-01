<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ----- Drip & prerequisites -----
        Schema::table('lessons', function (Blueprint $t) {
            $t->unsignedInteger('release_after_days')->default(0)->after('order');
        });

        Schema::table('courses', function (Blueprint $t) {
            $t->foreignId('prerequisite_course_id')->nullable()->after('teacher_id')
                ->constrained('courses')->nullOnDelete();
        });

        // ----- Quiz bank / randomisation -----
        Schema::table('quizzes', function (Blueprint $t) {
            $t->boolean('randomize')->default(false)->after('max_attempts');
            $t->unsignedSmallInteger('questions_per_attempt')->nullable()->after('randomize');
        });

        // ----- Referrals -----
        Schema::table('users', function (Blueprint $t) {
            $t->string('referral_code', 20)->nullable()->unique()->after('remember_token');
            $t->foreignId('referred_by')->nullable()->after('referral_code')
                ->constrained('users')->nullOnDelete();
        });

        Schema::create('referral_credits', function (Blueprint $t) {
            $t->id();
            $t->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('referred_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $t->decimal('amount', 10, 2);
            $t->string('currency', 8)->default('USD');
            $t->enum('status', ['pending', 'credited', 'paid'])->default('pending');
            $t->timestamps();
        });

        // ----- Gifting & subscription on payments -----
        Schema::table('payments', function (Blueprint $t) {
            $t->string('gift_token', 64)->nullable()->unique()->after('access_period');
            $t->string('gift_recipient_email')->nullable()->after('gift_token');
            $t->string('gift_recipient_name')->nullable()->after('gift_recipient_email');
            $t->text('gift_message')->nullable()->after('gift_recipient_name');
            $t->timestamp('gift_redeemed_at')->nullable()->after('gift_message');
            $t->foreignId('gift_redeemed_by')->nullable()->after('gift_redeemed_at')
                ->constrained('users')->nullOnDelete();
            $t->foreignId('subscription_id')->nullable()->after('gift_redeemed_by');
            $t->foreignId('bundle_id')->nullable()->after('subscription_id');
        });

        // ----- Subscriptions -----
        Schema::create('subscription_plans', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->text('description')->nullable();
            $t->decimal('price_usd', 10, 2)->default(0);
            $t->decimal('price_zwg', 10, 2)->default(0);
            $t->enum('interval', ['month', 'year'])->default('month');
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('plan_id')->constrained('subscription_plans');
            $t->enum('status', ['active', 'canceled', 'expired'])->default('active');
            $t->timestamp('started_at');
            $t->timestamp('expires_at');
            $t->timestamp('canceled_at')->nullable();
            $t->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $t->timestamps();
            $t->index(['user_id', 'status']);
        });

        // ----- Bundles / learning paths -----
        Schema::create('bundles', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('slug')->unique();
            $t->text('description')->nullable();
            $t->string('thumbnail')->nullable();
            $t->decimal('price_usd', 10, 2)->default(0);
            $t->decimal('price_zwg', 10, 2)->default(0);
            $t->boolean('is_active')->default(true);
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('bundle_course', function (Blueprint $t) {
            $t->id();
            $t->foreignId('bundle_id')->constrained()->cascadeOnDelete();
            $t->foreignId('course_id')->constrained()->cascadeOnDelete();
            $t->unsignedInteger('sort_order')->default(0);
            $t->unique(['bundle_id', 'course_id']);
        });

        // ----- Plagiarism flags -----
        Schema::create('assignment_similarity_flags', function (Blueprint $t) {
            $t->id();
            $t->foreignId('submission_id')->constrained('assignment_submissions')->cascadeOnDelete();
            $t->foreignId('compared_submission_id')->constrained('assignment_submissions')->cascadeOnDelete();
            $t->decimal('similarity_score', 5, 2); // 0..100
            $t->timestamps();
            $t->unique(['submission_id', 'compared_submission_id'], 'similarity_pair_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_similarity_flags');
        Schema::dropIfExists('bundle_course');
        Schema::dropIfExists('bundles');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('subscription_plans');
        Schema::dropIfExists('referral_credits');

        Schema::table('payments', function (Blueprint $t) {
            $t->dropConstrainedForeignId('gift_redeemed_by');
            $t->dropColumn(['gift_token', 'gift_recipient_email', 'gift_recipient_name', 'gift_message', 'gift_redeemed_at', 'subscription_id', 'bundle_id']);
        });

        Schema::table('users', function (Blueprint $t) {
            $t->dropConstrainedForeignId('referred_by');
            $t->dropColumn('referral_code');
        });

        Schema::table('quizzes', function (Blueprint $t) {
            $t->dropColumn(['randomize', 'questions_per_attempt']);
        });

        Schema::table('courses', function (Blueprint $t) {
            $t->dropConstrainedForeignId('prerequisite_course_id');
        });

        Schema::table('lessons', function (Blueprint $t) {
            $t->dropColumn('release_after_days');
        });
    }
};
