<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_flows', function (Blueprint $table) {
            $table->id();
            $table->string('flow_key')->unique();        // e.g. chiedza_register
            $table->string('meta_flow_id')->nullable();  // Meta-assigned ID after creation
            $table->string('name');
            $table->string('status')->default('DRAFT');  // DRAFT, PUBLISHED, DEPRECATED
            $table->json('categories')->nullable();
            $table->string('endpoint_uri')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_flows');
    }
};
