<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WhatsAppWebhookController;
use App\Http\Controllers\Api\ClientsSMSController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// مسار استقبال رسائل الواتساب الواردة
Route::post('/whatsapp/webhook', [WhatsAppWebhookController::class, 'handle']);

Route::post('/clients/check-status', [ClientsSMSController::class, 'checkStatus']);
Route::post('/clients/request-license', [ClientsSMSController::class, 'requestLicense']);

