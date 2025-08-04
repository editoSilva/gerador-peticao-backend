<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\Admin\PetitionController;
use App\Http\Controllers\Api\v1\Admin\JurisprudenceController;
use App\Http\Controllers\Api\v1\Admin\PetitionPriceController;
use App\Models\PetitionPrice;

Route::apiResource('jurisprudences', JurisprudenceController::class);
Route::apiResource('petitions', PetitionController::class);
Route::apiResource('prices', PetitionPriceController::class);