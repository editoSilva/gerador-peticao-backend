<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\Auth\AuthController;

Route::get( 'login', [AuthController::class, 'unauthenticated'])->name('login');


Route::group(['middleware' => ['auth:sanctum']], function() {
  
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('perfil', [AuthController::class, 'perfil']);
    Route::put('update/{id}', [AuthController::class, 'update']);
 

});



    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class,'resetPassword']);
   


