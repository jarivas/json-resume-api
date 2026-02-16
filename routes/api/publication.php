<?php

use App\Http\Controllers\Publication\Create;
use App\Http\Controllers\Publication\Delete;
use App\Http\Controllers\Publication\Read;
use App\Http\Controllers\Publication\Update;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('publication')
    ->group(function () {
        Route::post('', Create::class);
        Route::get('', Read::class);
        Route::patch('{publication}', Update::class)
            ->whereUlid('publication');
        Route::delete('{publication}', Delete::class)
            ->whereUlid('publication');
    });
