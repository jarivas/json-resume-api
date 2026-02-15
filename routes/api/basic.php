<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Basic\Create;
use App\Http\Controllers\Basic\Read;
use App\Http\Controllers\Basic\ReadOne;
use App\Http\Controllers\Basic\Update;
use App\Http\Controllers\Basic\Delete;

Route::middleware('auth:sanctum')->prefix('basic')
    ->group(function () {
        Route::post('', Create::class);
        Route::get('', Read::class);
        Route::get('{basic}', ReadOne::class);
        Route::patch('{basic}', Update::class);
        Route::delete('{basic}', Delete::class);
});