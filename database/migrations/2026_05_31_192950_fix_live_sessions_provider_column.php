<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Change from narrow enum('zoom','google_meet') to a plain VARCHAR
        // so all provider values (Zoom, Meet, Calendly, Other) are accepted.
        $driver = DB::connection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE live_sessions MODIFY COLUMN provider VARCHAR(50) NOT NULL DEFAULT 'Zoom'");
        } else {
            // SQLite & others: use Doctrine via Schema builder
            \Illuminate\Support\Facades\Schema::table('live_sessions', function ($t) {
                $t->string('provider', 50)->default('Zoom')->change();
            });
        }
    }

    public function down(): void
    {
        // Intentionally not reverting — cannot restore enum without data loss risk
    }
};
