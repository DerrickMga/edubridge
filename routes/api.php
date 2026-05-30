<?php

use App\Http\Controllers\Webhook\WhatsAppController;
use Illuminate\Support\Facades\Route;

Route::get('/webhook/whatsapp',  [WhatsAppController::class, 'verify']);
Route::post('/webhook/whatsapp', [WhatsAppController::class, 'receive']);

Route::prefix('v1')->middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    // API stubs — extend here as mobile clients are built
});
