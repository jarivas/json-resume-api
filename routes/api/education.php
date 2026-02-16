<?php

use App\Http\Controllers\Education\Create;
use App\Http\Controllers\Education\Delete;
use App\Http\Controllers\Education\Read;
use App\Http\Controllers\Education\Update;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('education')
    ->group(function () {
        Route::post('', Create::class);
        Route::get('', Read::class);
        Route::patch('{education}', Update::class)
            ->whereUlid('education');
        Route::delete('{education}', Delete::class)
            ->whereUlid('education');
    });
