<?php

use App\Http\Controllers\Language\Create;
use App\Http\Controllers\Language\Delete;
use App\Http\Controllers\Language\Read;
use App\Http\Controllers\Language\Update;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('language')
    ->group(function () {
        Route::post('', Create::class);
        Route::get('', Read::class);
        Route::patch('{language}', Update::class)
            ->whereUlid('language');
        Route::delete('{language}', Delete::class)
            ->whereUlid('language');
    });
