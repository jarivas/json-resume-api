<?php

use App\Http\Controllers\Volunteer\Create;
use App\Http\Controllers\Volunteer\Delete;
use App\Http\Controllers\Volunteer\Read;
use App\Http\Controllers\Volunteer\Update;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('volunteer')
    ->group(function () {
        Route::post('', Create::class);
        Route::get('', Read::class);
        Route::patch('{volunteer}', Update::class)
            ->whereUlid('volunteer');
        Route::delete('{volunteer}', Delete::class)
            ->whereUlid('volunteer');
    });
