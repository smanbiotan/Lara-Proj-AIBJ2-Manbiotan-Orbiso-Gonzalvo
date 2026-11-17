<?php

use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\N8nToolController;
use App\Http\Controllers\Api\PublicApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public JSON API (used by the website JavaScript) — prefix /api
|--------------------------------------------------------------------------
*/
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/business/services', [PublicApiController::class, 'services']);
    Route::get('/appointments/availability', [PublicApiController::class, 'availability']);
});
Route::post('/appointments', [PublicApiController::class, 'storeAppointment'])->middleware('throttle:10,1');
Route::post('/chat', [ChatController::class, 'send'])->middleware('throttle:20,1');

/*
|--------------------------------------------------------------------------
| Private tools for n8n — require "Authorization: Bearer N8N_API_TOKEN"
|--------------------------------------------------------------------------
*/
Route::prefix('n8n')->middleware(['n8n.token', 'throttle:120,1'])->group(function () {
    Route::get('/services', [N8nToolController::class, 'services']);
    Route::get('/business-info', [N8nToolController::class, 'businessInfo']);
    Route::get('/availability', [N8nToolController::class, 'availability']);
    Route::post('/appointments', [N8nToolController::class, 'createAppointment']);
    Route::post('/appointments/status', [N8nToolController::class, 'appointmentStatus']);
    Route::post('/escalations', [N8nToolController::class, 'escalate']);
    Route::post('/reminders/claim', [N8nToolController::class, 'claimReminders']);
    Route::post('/automation-results', [N8nToolController::class, 'automationResult']);
});
