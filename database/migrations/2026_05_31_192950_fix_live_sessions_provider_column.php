<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Change from narrow enum('zoom','google_meet') to a plain VARCHAR
        // so all provider values (Zoom, Meet, Calendly, Other) are accepted.
        DB::statement("ALTER TABLE live_sessions MODIFY COLUMN provider VARCHAR(50) NOT NULL DEFAULT 'Zoom'");
    }

    public function down(): void
    {
        // Intentionally not reverting — cannot restore enum without data loss risk
    }
};
