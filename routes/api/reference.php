<?php

use App\Http\Controllers\Reference\Create;
use App\Http\Controllers\Reference\Delete;
use App\Http\Controllers\Reference\Read;
use App\Http\Controllers\Reference\Update;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('reference')
    ->group(function () {
        Route::post('', Create::class);
        Route::get('', Read::class);
        Route::patch('{reference}', Update::class)
            ->whereUlid('reference');
        Route::delete('{reference}', Delete::class)
            ->whereUlid('reference');
    });
