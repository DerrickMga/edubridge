<?php

use App\Http\Controllers\Webhook\WhatsAppController;
use Illuminate\Support\Facades\Route;

// ─── WhatsApp Cloud API webhook ────────────────────────────────────────────────
Route::get('/webhook/whatsapp',  [WhatsAppController::class, 'verify']);
Route::post('/webhook/whatsapp', [WhatsAppController::class, 'receive']);
