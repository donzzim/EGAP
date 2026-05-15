<?php

use App\Http\Controllers\Api\BensController;
use App\Http\Controllers\Api\MobileAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('mobile-api')->group(function (): void {
    Route::post('/login', [MobileAuthController::class, 'login'])
        ->name('mobile-login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [MobileAuthController::class, 'me'])->name('mobile.me');

        Route::post('/logout', [MobileAuthController::class, 'logout'])->name('mobile.logout');

        Route::get('/bens', [BensController::class, 'index'])->name('mobile.bens');
        Route::get('/bens/{numPatrimonio}', [BensController::class, 'show'])->name('mobile.bens.show');
    });
});
