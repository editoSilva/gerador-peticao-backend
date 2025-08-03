<?php

use App\Http\Controllers\Api\v1\Webhook\MP\WebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('mp')->group( function() {
    Route::post('updatepayment', [WebhookController::class, 'updatePayment']);
});
