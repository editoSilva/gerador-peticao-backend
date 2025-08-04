<?php

use App\Http\Controllers\Api\v1\Admin\JurisprudenceController;
use App\Http\Controllers\Api\v1\Costumer\PetitionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('auth')->group(base_path('routes/Auth/api.php'));


Route::prefix('costumer')->group(function () {
    Route::apiResource('petitions', PetitionController::class);
});

Route::prefix('webhooks')->group(
    base_path('routes/Webhooks/MP/api.php')
);

Route::prefix('admin')->middleware(['auth:sanctum'])->group(
    base_path('routes/Admin/api.php'),
);

Route::get('/', function() {
    return response()->json([
        'message' => 'Welcome to our API Petitions endpoint.',
        'status' => 200
    ]);
});
