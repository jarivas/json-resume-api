<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Authentication\Login;
use App\Http\Controllers\Authentication\Logout;
use App\Http\Controllers\Authentication\Recovery;
use App\Http\Controllers\Authentication\ChangePassword;

Route::prefix('authentication')->group(function () {
    Route::post('/login', Login::class);
    Route::post('/recovery', Recovery::class);
    Route::post('/change-password', ChangePassword::class);
    Route::middleware('auth:sanctum')
        ->post('/logout', Logout::class);
});