<?php

use App\Http\Controllers\Education\Create;
use App\Http\Controllers\Education\Delete;
use App\Http\Controllers\Education\Read;
use App\Http\Controllers\Education\Update;
use App\Http\Controllers\Education\Attach;
use App\Http\Controllers\Education\Detach;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('education')
    ->group(function () {
        Route::post('', Create::class);
        Route::get('', Read::class);
        Route::patch('{education}', Update::class);
        Route::delete('{education}', Delete::class);
        Route::post('{education}/attach/{basic}', Attach::class);
        Route::post('{education}/detach/{basic}', Detach::class);
    });
