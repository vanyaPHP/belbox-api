<?php

use App\Http\Controllers\V1\Identity\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('/auth')->name('auth.')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('/login', 'login')->name('login');
        Route::middleware(['auth:sanctum'])->group(function () {
            Route::get('/me', 'me')->name('me');
            Route::post('/logout', 'logout')->name('logout');
        });
    });
});
