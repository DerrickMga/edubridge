<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Remove any existing duplicate rows before adding the unique index.
        // Keep the first (lowest id) record for each message_id.
        DB::statement('
            DELETE w1 FROM whatsapp_webhooks w1
            INNER JOIN whatsapp_webhooks w2
                ON w1.message_id = w2.message_id
                AND w1.id > w2.id
            WHERE w1.message_id IS NOT NULL
        ');

        Schema::table('whatsapp_webhooks', function (Blueprint $table) {
            $table->unique('message_id', 'whatsapp_webhooks_message_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_webhooks', function (Blueprint $table) {
            $table->dropUnique('whatsapp_webhooks_message_id_unique');
        });
    }
};
