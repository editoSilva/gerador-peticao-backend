<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\Webhook\Asas\WebhookController;


Route::prefix('asas')->group( function() {
    Route::post('payment-confirm', [WebhookController::class, 'updatePayment']);
});
