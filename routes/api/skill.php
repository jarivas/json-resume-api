<?php

use App\Http\Controllers\Skill\Create;
use App\Http\Controllers\Skill\Delete;
use App\Http\Controllers\Skill\Read;
use App\Http\Controllers\Skill\Update;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('skill')
    ->group(function () {
        Route::post('', Create::class);
        Route::get('', Read::class);
        Route::patch('{skill}', Update::class)
            ->whereUlid('skill');
        Route::delete('{skill}', Delete::class)
            ->whereUlid('skill');
    });
