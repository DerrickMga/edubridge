<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('whatsapp_webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('from_phone', 20);
            $table->string('message_id')->nullable();
            $table->enum('type', ['text', 'image', 'audio', 'video', 'document', 'status'])->default('text');
            $table->text('content')->nullable();
            $table->json('payload');
            $table->boolean('processed')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('whatsapp_webhooks'); }
};
