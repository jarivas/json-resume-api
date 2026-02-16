<?php

use App\Http\Controllers\Certificate\Create;
use App\Http\Controllers\Certificate\Delete;
use App\Http\Controllers\Certificate\Read;
use App\Http\Controllers\Certificate\Update;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('certificate')
    ->group(function () {
        Route::post('', Create::class);
        Route::get('', Read::class);
        Route::patch('{certificate}', Update::class)
            ->whereUlid('certificate');
        Route::delete('{certificate}', Delete::class)
            ->whereUlid('certificate');
    });
