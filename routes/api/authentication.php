<?php

use App\Http\Controllers\Authentication\ChangePassword;
use App\Http\Controllers\Authentication\Login;
use App\Http\Controllers\Authentication\Logout;
use App\Http\Controllers\Authentication\Recovery;
use App\Http\Controllers\Authentication\RefreshToken;
use Illuminate\Support\Facades\Route;

Route::prefix('authentication')->group(function () {
    Route::post('/login', Login::class);
    Route::post('/recovery', Recovery::class);
    Route::post('/change-password', ChangePassword::class);
    Route::middleware('auth:sanctum')
        ->post('/refresh-token', RefreshToken::class);
    Route::middleware('auth:sanctum')
        ->post('/logout', Logout::class);
});
