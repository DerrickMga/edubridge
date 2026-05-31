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
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Seed default platform pricing
        DB::table('settings')->insert([
            ['key' => 'price_hourly',  'value' => '1.00',  'created_at' => now(), 'updated_at' => now()],
            ['key' => 'price_monthly', 'value' => '5.00',  'created_at' => now(), 'updated_at' => now()],
            ['key' => 'price_termly',  'value' => '10.00', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'price_zwg_rate','value' => '30',    'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
