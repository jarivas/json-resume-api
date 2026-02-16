<?php

use App\Http\Controllers\Project\Create;
use App\Http\Controllers\Project\Delete;
use App\Http\Controllers\Project\Read;
use App\Http\Controllers\Project\Update;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('project')
    ->group(function () {
        Route::post('', Create::class);
        Route::get('', Read::class);
        Route::patch('{project}', Update::class)
            ->whereUlid('project');
        Route::delete('{project}', Delete::class)
            ->whereUlid('project');
    });
