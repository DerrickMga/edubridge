<?php

use App\Http\Controllers\Webhook\WhatsAppController;
use App\Http\Controllers\Webhook\WhatsAppFlowController;
use Illuminate\Support\Facades\Route;

Route::get('/webhook/whatsapp',  [WhatsAppController::class, 'verify']);
Route::post('/webhook/whatsapp', [WhatsAppController::class, 'receive']);

// WhatsApp Flow encrypted data-exchange endpoint
// Must be excluded from CSRF — handled via api middleware group (stateless)
Route::post('/whatsapp/flow', [WhatsAppFlowController::class, 'handle']);

Route::prefix('v1')->middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    // API stubs — extend here as mobile clients are built
});
