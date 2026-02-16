<?php

use App\Http\Controllers\Award\Attach;
use App\Http\Controllers\Award\Create;
use App\Http\Controllers\Award\Delete;
use App\Http\Controllers\Award\Detach;
use App\Http\Controllers\Award\Read;
use App\Http\Controllers\Award\Update;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('award')
    ->group(function () {
        Route::post('', Create::class);
        Route::get('', Read::class);
        Route::patch('{award}', Update::class)
            ->whereUlid('award');
        Route::delete('{award}', Delete::class)
            ->whereUlid('award');
        Route::post('{award}/attach', Attach::class)
            ->whereUlid(['award', 'basic']);
        Route::post('{award}/detach', Detach::class)
            ->whereUlid(['award', 'basic']);
    });
