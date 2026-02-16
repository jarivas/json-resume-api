<?php

use App\Http\Controllers\Work\Create;
use App\Http\Controllers\Work\Delete;
use App\Http\Controllers\Work\Read;
use App\Http\Controllers\Work\Update;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('work')
    ->group(function () {
        Route::post('', Create::class);
        Route::get('', Read::class);
        Route::patch('{work}', Update::class)
            ->whereUlid('work');
        Route::delete('{work}', Delete::class)
            ->whereUlid('work');
    });
