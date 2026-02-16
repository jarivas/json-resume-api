<?php

use App\Http\Controllers\Skill\Create;
use App\Http\Controllers\Skill\Delete;
use App\Http\Controllers\Skill\Read;
use App\Http\Controllers\Skill\Update;
use App\Http\Controllers\Skill\Attach;
use App\Http\Controllers\Skill\Detach;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('skill')
    ->group(function () {
        Route::post('', Create::class);
        Route::get('', Read::class);
        Route::patch('{skill}', Update::class);
        Route::delete('{skill}', Delete::class);
        Route::post('{skill}/attach/{basic}', Attach::class);
        Route::post('{skill}/detach/{basic}', Detach::class);
    });
