<?php

use App\Http\Controllers\Interest\Create;
use App\Http\Controllers\Interest\Delete;
use App\Http\Controllers\Interest\Read;
use App\Http\Controllers\Interest\Update;
use App\Http\Controllers\Interest\Attach;
use App\Http\Controllers\Interest\Detach;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('interest')
    ->group(function () {
        Route::post('', Create::class);
        Route::get('', Read::class);
        Route::patch('{interest}', Update::class);
        Route::delete('{interest}', Delete::class);
        Route::post('{interest}/attach/{basic}', Attach::class);
        Route::post('{interest}/detach/{basic}', Detach::class);
    });
