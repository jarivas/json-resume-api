<?php

use App\Http\Controllers\Reference\Create;
use App\Http\Controllers\Reference\Delete;
use App\Http\Controllers\Reference\Read;
use App\Http\Controllers\Reference\Update;
use App\Http\Controllers\Reference\Attach;
use App\Http\Controllers\Reference\Detach;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('reference')
    ->group(function () {
        Route::post('', Create::class);
        Route::get('', Read::class);
        Route::patch('{reference}', Update::class);
        Route::delete('{reference}', Delete::class);
        Route::post('{reference}/attach/{basic}', Attach::class);
        Route::post('{reference}/detach/{basic}', Detach::class);
    });
