<?php

use App\Http\Controllers\Language\Create;
use App\Http\Controllers\Language\Delete;
use App\Http\Controllers\Language\Read;
use App\Http\Controllers\Language\Update;
use App\Http\Controllers\Language\Attach;
use App\Http\Controllers\Language\Detach;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('language')
    ->group(function () {
        Route::post('', Create::class);
        Route::get('', Read::class);
        Route::patch('{language}', Update::class);
        Route::delete('{language}', Delete::class);
        Route::post('{language}/attach/{basic}', Attach::class);
        Route::post('{language}/detach/{basic}', Detach::class);
    });
