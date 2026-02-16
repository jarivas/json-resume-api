<?php

use App\Http\Controllers\Interest\Create;
use App\Http\Controllers\Interest\Delete;
use App\Http\Controllers\Interest\Read;
use App\Http\Controllers\Interest\Update;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('interest')
    ->group(function () {
        Route::post('', Create::class);
        Route::get('', Read::class);
        Route::patch('{interest}', Update::class)
            ->whereUlid('interest');
        Route::delete('{interest}', Delete::class)
            ->whereUlid('interest');
    });
