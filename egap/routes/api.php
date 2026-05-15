<?php

use App\Http\Controllers\Api\MobileLoginController;
use Illuminate\Support\Facades\Route;

Route::prefix('mobile-api')->group(function (): void {
    Route::post('/auth/login', [MobileLoginController::class, 'store'])
        ->name('mobile-api.login');
});
