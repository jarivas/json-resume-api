<?php

use App\Http\Controllers\Certificate\Create;
use App\Http\Controllers\Certificate\Delete;
use App\Http\Controllers\Certificate\Read;
use App\Http\Controllers\Certificate\Update;
use App\Http\Controllers\Certificate\Attach;
use App\Http\Controllers\Certificate\Detach;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('certificate')
    ->group(function () {
        Route::post('', Create::class);
        Route::get('', Read::class);
        Route::patch('{certificate}', Update::class);
        Route::delete('{certificate}', Delete::class);
        Route::post('{certificate}/attach/{basic}', Attach::class);
        Route::post('{certificate}/detach/{basic}', Detach::class);
    });
