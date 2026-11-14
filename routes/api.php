<?php

use App\Http\Controllers\Api\V1\ClientCommentController;
use App\Http\Controllers\Api\V1\ClientTicketController;
use App\Http\Controllers\Api\V1\PmsWebhookController;
use App\Http\Controllers\Api\V1\TicketIngestController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('tickets', [TicketIngestController::class, 'store'])
        ->middleware(['auth:sanctum', 'throttle:ticket-ingestion'])
        ->name('api.tickets.store');

    Route::get('tickets/{ticketNumber}', [ClientTicketController::class, 'show'])
        ->middleware('throttle:client-tracking')
        ->name('api.tickets.show');

    Route::post('tickets/{ticketNumber}/comments', [ClientCommentController::class, 'store'])
        ->middleware('throttle:client-tracking')
        ->name('api.tickets.comments.store');

    Route::post('webhooks/pms/{provider}', [PmsWebhookController::class, 'store'])
        ->middleware('throttle:public-webhooks')
        ->name('api.webhooks.pms');
});
