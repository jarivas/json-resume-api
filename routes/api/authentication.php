<?php

use Illuminate\Support\Facades\Route;

Route::prefix('authentication')->group(function () {
    Route::post('/login', \App\Http\Controllers\Authentication\Login::class);
    Route::post('/recovery', \App\Http\Controllers\Authentication\Recovery::class);
    Route::post('/change-password', \App\Http\Controllers\Authentication\ChangePassword::class);
    Route::middleware('auth:sanctum')
    ->post('/logout', \App\Http\Controllers\Authentication\Logout::class);
});