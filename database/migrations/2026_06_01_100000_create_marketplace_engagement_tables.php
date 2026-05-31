<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Courses: rating cache cols
        Schema::table('courses', function (Blueprint $t) {
            if (! Schema::hasColumn('courses', 'average_rating'))   $t->decimal('average_rating', 3, 2)->default(0)->after('price_zwg');
            if (! Schema::hasColumn('courses', 'reviews_count'))    $t->unsignedInteger('reviews_count')->default(0)->after('average_rating');
        });

        // Submissions: who graded
        Schema::table('assignment_submissions', function (Blueprint $t) {
            if (! Schema::hasColumn('assignment_submissions', 'graded_by')) {
                $t->foreignId('graded_by')->nullable()->after('feedback')->constrained('users')->nullOnDelete();
            }
        });

        // Course reviews
        Schema::create('course_reviews', function (Blueprint $t) {
            $t->id();
            $t->foreignId('course_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->unsignedTinyInteger('rating'); // 1..5
            $t->string('title', 200)->nullable();
            $t->text('body')->nullable();
            $t->boolean('is_visible')->default(true);
            $t->timestamps();
            $t->unique(['course_id', 'user_id']);
        });

        // Wishlist
        Schema::create('wishlists', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('course_id')->constrained()->cascadeOnDelete();
            $t->timestamps();
            $t->unique(['user_id', 'course_id']);
        });

        // Coupons
        Schema::create('coupons', function (Blueprint $t) {
            $t->id();
            $t->string('code', 40)->unique();
            $t->string('description', 200)->nullable();
            $t->enum('type', ['percent', 'fixed'])->default('percent');
            $t->decimal('value', 10, 2);
            $t->string('currency', 8)->default('USD'); // applies to fixed type
            $t->foreignId('course_id')->nullable()->constrained()->nullOnDelete(); // null = global
            $t->unsignedInteger('max_redemptions')->nullable();
            $t->unsignedInteger('per_user_limit')->default(1);
            $t->decimal('min_order_value', 10, 2)->nullable();
            $t->timestamp('starts_at')->nullable();
            $t->timestamp('expires_at')->nullable();
            $t->boolean('is_active')->default(true);
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        // Coupon redemptions (audit + per-user limit)
        Schema::create('coupon_redemptions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $t->decimal('discount_amount', 10, 2);
            $t->string('currency', 8)->default('USD');
            $t->timestamps();
        });

        // Payments: coupon link
        Schema::table('payments', function (Blueprint $t) {
            if (! Schema::hasColumn('payments', 'coupon_id'))       $t->foreignId('coupon_id')->nullable()->after('metadata')->constrained()->nullOnDelete();
            if (! Schema::hasColumn('payments', 'discount_amount')) $t->decimal('discount_amount', 10, 2)->default(0)->after('coupon_id');
            if (! Schema::hasColumn('payments', 'subtotal_amount')) $t->decimal('subtotal_amount', 10, 2)->nullable()->after('discount_amount');
        });

        // Refund requests
        Schema::create('refund_requests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $t->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $t->decimal('amount', 10, 2);
            $t->string('currency', 8)->default('USD');
            $t->enum('status', ['pending', 'approved', 'rejected', 'processed'])->default('pending');
            $t->text('reason');
            $t->text('admin_notes')->nullable();
            $t->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('decided_at')->nullable();
            $t->timestamps();
        });

        // Lesson notes (per-timestamp)
        Schema::create('lesson_notes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $t->foreignId('course_id')->constrained()->cascadeOnDelete();
            $t->unsignedInteger('timestamp_seconds')->nullable(); // null = general note
            $t->text('body');
            $t->timestamps();
            $t->index(['user_id', 'lesson_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_notes');
        Schema::dropIfExists('refund_requests');
        Schema::dropIfExists('coupon_redemptions');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('course_reviews');

        Schema::table('payments', function (Blueprint $t) {
            foreach (['coupon_id', 'discount_amount', 'subtotal_amount'] as $c) {
                if (Schema::hasColumn('payments', $c)) {
                    try { $t->dropConstrainedForeignId($c); } catch (\Throwable $e) {}
                    if (Schema::hasColumn('payments', $c)) $t->dropColumn($c);
                }
            }
        });
        Schema::table('assignment_submissions', function (Blueprint $t) {
            if (Schema::hasColumn('assignment_submissions', 'graded_by')) {
                try { $t->dropConstrainedForeignId('graded_by'); } catch (\Throwable $e) { $t->dropColumn('graded_by'); }
            }
        });
        Schema::table('courses', function (Blueprint $t) {
            foreach (['average_rating', 'reviews_count'] as $c) {
                if (Schema::hasColumn('courses', $c)) $t->dropColumn($c);
            }
        });
    }
};
