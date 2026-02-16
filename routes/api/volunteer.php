<?php

use App\Http\Controllers\Volunteer\Create;
use App\Http\Controllers\Volunteer\Delete;
use App\Http\Controllers\Volunteer\Read;
use App\Http\Controllers\Volunteer\Update;
use App\Http\Controllers\Volunteer\Attach;
use App\Http\Controllers\Volunteer\Detach;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('volunteer')
    ->group(function () {
        Route::post('', Create::class);
        Route::get('', Read::class);
        Route::patch('{volunteer}', Update::class);
        Route::delete('{volunteer}', Delete::class);
        Route::post('{volunteer}/attach/{basic}', Attach::class);
        Route::post('{volunteer}/detach/{basic}', Detach::class);
    });
