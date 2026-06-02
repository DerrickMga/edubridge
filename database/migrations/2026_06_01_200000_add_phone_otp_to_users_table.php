<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone_otp_code')->nullable()->after('phone');
            $table->timestamp('phone_otp_expires_at')->nullable()->after('phone_otp_code');
            $table->timestamp('phone_verified_at')->nullable()->after('phone_otp_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone_otp_code', 'phone_otp_expires_at', 'phone_verified_at']);
        });
    }
};
