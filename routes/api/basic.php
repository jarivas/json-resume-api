<?php

use App\Http\Controllers\Basic\Create;
use App\Http\Controllers\Basic\Delete;
use App\Http\Controllers\Basic\Read;
use App\Http\Controllers\Basic\ReadOne;
use App\Http\Controllers\Basic\Update;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('basic')
    ->group(function () {
        Route::post('', Create::class);
        Route::get('', Read::class);
        Route::get('{basic}', ReadOne::class)
            ->whereUlid('basic');
        Route::patch('{basic}', Update::class)
            ->whereUlid('basic');
        Route::delete('{basic}', Delete::class)
            ->whereUlid('basic');
    });
