<?php

use App\Http\Controllers\Publication\Create;
use App\Http\Controllers\Publication\Delete;
use App\Http\Controllers\Publication\Read;
use App\Http\Controllers\Publication\Update;
use App\Http\Controllers\Publication\Attach;
use App\Http\Controllers\Publication\Detach;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('publication')
    ->group(function () {
        Route::post('', Create::class);
        Route::get('', Read::class);
        Route::patch('{publication}', Update::class);
        Route::delete('{publication}', Delete::class);
        Route::post('{publication}/attach/{basic}', Attach::class);
        Route::post('{publication}/detach/{basic}', Detach::class);
    });
